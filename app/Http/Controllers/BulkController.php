<?php

namespace App\Http\Controllers;

use App\Channels\ChannelFactory;
use App\Models\Room;
use App\Models\Property;
use App\Models\PropertiesChannel;
use App\Models\InventoryMap;

class BulkController extends Controller
{

    /**
     * Display a listing of the resource.
     * GET /bulk
     *
     * @return Response
     */
    public function getIndex()
    {
        $rooms = Room::forBulkRate(Property::getLoggedId())->get();
        return View::make('bulk.rates', compact('rooms'));
    }

    /**
     * Display a listing of the resource.
     * GET /bulk
     *
     * @return Response
     */
    public function getAvailability()
    {
        $rooms = Room::forBulkAvailability(Property::getLoggedId())->get();
        return View::make('bulk.availability', compact('rooms'));
    }

    public function postUpdateAvailability()
    {
        $rules = [
            'from_date' => 'required|date_format:Y-m-d|after:' . date('Y-m-d', strtotime('yesterday')),
            'to_date' => 'required|date_format:Y-m-d|after:' . date('Y-m-d', strtotime('yesterday')),
            'week_day' => 'required',
            'rooms' => 'required',
            'availability' => 'required|integer|min:0',
        ];
        $validator = Validator::make($data = Input::all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ], 400); //400 - http error code
        }

        //all ok so get rooms and plans mapping

        $weekDays = ($data['week_day']);

        $errors = [];
        $property = Property::findOrFail(Property::getLoggedId());
        foreach ($data['rooms'] as $roomId) {
            //get room data
            $room = Room::findOrFail($roomId);
            $depth = 0;
            $this->updateChannelAvailability($room, $property, $data, $data['availability'], $weekDays, $errors, $depth);

        }

        if (!empty($errors)) {
            return Response::json([
                'success' => false,
                'errors' => $errors
            ], 400); //400 - http error code
        }


        return Response::json([
            'success' => true,
        ], 200); //200 - http success code
    }

    /**
     * Recursive function
     * TODO: move to another place
     * @param Room $room
     * @param Property $property
     * @param $data
     * @param $availability - rate value for update chanel
     * @param $weekDays
     * @param $errors
     * @param $depth
     */
    function updateChannelAvailability($room, $property, $data, $availability, $weekDays, &$errors, &$depth)
    {
        Log::info($room);
        if ($depth > 5) {//infinity loop protection
            return;
        }
        //get plan mapping
        $maps = InventoryMap::getByKeys(null, $property->id, $room->id)->distinct()->get(['inventory_code', 'channel_id', 'property_id']);
        foreach ($maps as $mapping) {
            //get channel
            $channelSettings = PropertiesChannel::getSettings($mapping->channel_id, $mapping->property_id);
            $channel = ChannelFactory::create($channelSettings);
            $channel->setCurrency($property->currency);
            //updating rates

            $result = $channel->setAvailability($mapping->inventory_code, $data['from_date'], $data['to_date'], $weekDays, $availability);
            if (is_array($result)) {
                $formattedErrors = [];
                foreach ($result as $error) {
                    $formattedErrors[] = $channelSettings->channel()->name . ': ' . $error;
                }
                $errors += $formattedErrors;
            }
        }
        //check if children rooms exist
        if ($children = $room->plans()->get()) {
            if (!$children->isEmpty()) {
                $depth++;
                foreach ($children as $child) {
                    $this->updateChannelAvailability($child, $property, $data, $availability, $weekDays, $errors, $depth);
                }
            }
        }
    }


    public function postUpdateRates()
    {
        $rules = [
            'from_date' => 'required|date_format:Y-m-d|after:' . date('Y-m-d', strtotime('yesterday')),
            'to_date' => 'required|date_format:Y-m-d|after:' . date('Y-m-d', strtotime('yesterday')),
            'week_day' => 'required',
            'rooms' => 'required',
            'rate' => 'required|numeric|min:0',
            'single_rate' => 'numeric|min:0'
        ];
        $validator = Validator::make($data = Input::all(), $rules);

        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ], 400); //400 - http error code
        }

        //all ok so get rooms and plans mapping

        $weekDays = ($data['week_day']);

        $errors = [];
        $property = Property::findOrFail(Property::getLoggedId());

        foreach ($data['rooms'] as $roomId) {
            //get room data
            $room = Room::findOrFail($roomId);
            $depth = 0;
            $this->updateChannelRate($room, $property, $data, $data['rate'], $weekDays, $errors, $depth);

        }

        if (!empty($errors)) {
            return Response::json([
                'success' => false,
                'errors' => $errors
            ], 400); //400 - http error code
        }


        return Response::json([
            'success' => true,
        ], 200); //200 - http success code
    }


    /**
     * Recursive function
     * TODO: move to another place
     * @param Room $room
     * @param Property $property
     * @param $data
     * @param $rate - rate value for update chanel
     * @param $weekDays
     * @param $errors
     * @param $depth
     */
    function updateChannelRate($room, $property, $data, $rate, $weekDays, &$errors, &$depth)
    {
        if ($depth > 5) {//infinity loop protection
            return;
        }

        //get plan mapping
        $maps = InventoryMap::getByKeys(null, $property->id, $room->id)->get();
        foreach ($maps as $mapping) {
            //get channel
            $channelSettings = PropertiesChannel::getSettings($mapping->channel_id, $mapping->property_id);
            if (!$channelSettings) {
                continue;
            }
            $channel = ChannelFactory::create($channelSettings);
            $channel->setCurrency($property->currency);
            //updating rates

            $result = $channel->setRate(
                $mapping->inventory_code, $mapping->plan_code,
                $data['from_date'], $data['to_date'], $weekDays,
                $rate, isset($data['single_rate']) ? $data['single_rate'] : null
            );
            if (is_array($result)) {
                $formattedErrors = [];
                foreach ($result as $error) {
                    $formattedErrors[] = $channelSettings->channel()->name . ': ' . $error;
                }
                $errors += $formattedErrors;
            }
        }
        //check if children rooms exist
        if ($children = $room->children()->get()) {
            if (!$children->isEmpty()) {
                $depth++;
                //so we go deep so lets do rate of current ROOM as default rate,
                //like if we directly set this rate in form
                $data['rate'] = $rate;
                foreach ($children as $child) {
                    switch ($child->formula_type) {
                        case 'x':
                            $rate = $data['rate'] * $child->formula_value;
                            break;
                        case '+':
                            $rate = $data['rate'] + $child->formula_value;
                            break;
                        case '-':
                            $rate = $data['rate'] - $child->formula_value;
                            break;
                    }
                    $this->updateChannelRate($child, $property, $data, $rate, $weekDays, $errors, $depth);
                }
            }
        }
    }
}