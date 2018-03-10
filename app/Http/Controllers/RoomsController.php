<?php

namespace App\Http\Controllers;

use App\Channels\ChannelFactory;
use App\Models\Room;
use App\Models\Property;
use App\Models\PropertiesChannel;
use App\Models\Channel;
use App\Models\Inventory;
use App\Models\InventoryMap;
use App\Models\InventoryPlan;

class RoomsController extends Controller
{
    /**
     * Display a listing of rooms
     *
     * @return Response
     */
    public function getIndex()
    {
        $rooms = Room::where('property_id', Property::getLoggedId())->get();

        $channels = PropertiesChannel::where('property_id', Property::getLoggedId())->get();

        return View::make('rooms.index', compact('rooms', 'channels'));
    }

    /**
     * Show the form for creating a new room
     *
     * @return Response
     */
    public function getCreate()
    {
        $rooms = Room::where('property_id', Property::getLoggedId())->where('type', '<>', 'plan')->lists('name', 'id');

        $properties = Property::lists('name', 'id');
        $formulaTypes = Room::$formulaTypes;

        return view('rooms.create', compact('properties', 'rooms', 'formulaTypes'));
    }

    /**
     * Store a newly created room in storage.
     *
     * @return Response
     */
    public function postStore()
    {
        $validator = Validator::make($data = Input::all(), Room::$rules);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        $data['property_id'] = Property::getLoggedId();

        Room::create($data);

        return redirect()->to('RoomsController@getIndex');
    }

    /**
     * Display the specified room.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $room = Room::findOrFail($id);

        return view('rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified room.
     *
     * @param  int $id
     * @return Response
     */
    public function getEdit($id)
    {
        $room = Room::find($id);
        $rooms = Room::where('property_id', Property::getLoggedId())
            ->where('type', '<>', 'plan')
            ->where('id', '<>', $id)
            ->lists('name', 'id');
        $formulaTypes = Room::$formulaTypes;
        return view('rooms.edit', compact('room', 'formulaTypes', 'rooms'));
    }

    /**
     * Show the form for editing the specified room.
     *
     * @param int $id
     * @param int $channelId
     * @return Response
     */
    public function getMap($id, $channelId)
    {
        $room = Room::find($id);

        $channelSettings = PropertiesChannel::getSettings($channelId, Property::getLoggedId());
        $channel = ChannelFactory::create($channelSettings);
        $result = $channel->getInventoryList();//todo temp
//        file_put_contents('1.txt', serialize($result));
//        $result = unserialize(file_get_contents('1.txt'));


        //add Inventories and Plans to DB//TODO move to another place
        //delete exist maps
        Inventory::where([
            'channel_id' => $channelId,
            'property_id' => $channelSettings->property_id,
        ])->delete();
        //delete exist plan maps
        InventoryPlan::where([
            'channel_id' => $channelId,
            'property_id' => $channelSettings->property_id,
        ])->delete();


        if ($result) {
            foreach ($result as $inventory) {
                Inventory::create([
                    'code' => $inventory['code'],
                    'name' => $inventory['name'],
                    'channel_id' => $channelId,
                    'property_id' => $channelSettings->property_id,
                ]);

                if ($inventory['plans']) {
                    foreach ($inventory['plans'] as $plan) {
                        InventoryPlan::create([
                            'code' => $plan['code'],
                            'name' => $plan['name'],
                            'channel_id' => $channelId,
                            'inventory_code' => $inventory['code'],
                            'property_id' => $channelSettings->property_id,
                        ]);
                    }
                }
            }
        }

        $existMapping = [];
//        $mapCollection = InventoryMap::where(
//            [
//                'channel_id' => $channelId,
//                'property_id' => $channelSettings->property_id
//            ]
//        )
////            ->where('room_id', '<>', $id)
//            ->lists('inventory_code', 'room_id');
//        if ($mapCollection) {
//            foreach ($mapCollection as $map) {
//                $existMapping[] = $map;
//            }
//        }>F
        $inventories = Channel::find($channelId)->inventory()->where('property_id', Property::getLoggedId());

        $inventoryList = [];
        $inventoryPlans = [];
        foreach ($inventories->get() as $inventory) {
//            if (in_array($inventory->code, $existMapping)) {
//                continue;
//            }
            $inventoryList[$inventory->code] = $inventory->name;

            $plans = $inventory->plans()->get(['name', 'code']);
            for ($i = 0; $i < count($plans); $i++) {
                //TODO rewrite to ONE query
                $plans[$i]->selected = InventoryMap::getByKeys($channelId, $channelSettings->property_id, $id, $plans[$i]['code'])->first() ? true : false;
            }
            $inventoryPlans[$inventory->code] = $plans;
        }
        $inventoryPlans = json_encode($inventoryPlans);

        $mapping = InventoryMap::getByKeys($channelId, $channelSettings->property_id, $id)->first();
        return View::make('rooms.map', compact('room', 'channel', 'inventoryList', 'inventoryPlans', 'channelId', 'mapping'));
    }

    /**
     * Update the specified room in storage.
     *
     * @return Response
     */
    public function postMap()
    {
        $validator = Validator::make($data = Input::all(), InventoryMap::$rules);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        $data['property_id'] = Property::getLoggedId();


        InventoryMap::getByKeys($data['channel_id'], $data['property_id'], $data['room_id'])->delete();

        //get inventory room name
        if ($data['code'] && $data['plans']) {
            $inventory = Inventory::getByKeys($data['channel_id'], $data['property_id'], $data['code'])->first();
            if ($inventory) {
                $preparedData = [
                    'name' => $inventory->name,
                    'room_id' => $data['room_id'],
                    'inventory_code' => $data['code'],
                    'channel_id' => $data['channel_id'],
                    'property_id' => $data['property_id'],
                ];

                foreach ($data['plans'] as $planCode) {
                    $plan = InventoryPlan::getByKeys($data['channel_id'], $data['property_id'], $planCode)->first();

                    if ($plan) {
                        $preparedData['plan_code'] = $plan->code;
                        $preparedData['plan_name'] = $plan->name;
                        InventoryMap::create($preparedData);
                    }
                }
            }
        }


        return Redirect::action('RoomsController@getIndex');
    }

    /**
     * Update the specified room in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function postUpdate($id)
    {
        $room = Room::findOrFail($id);

        $validator = Validator::make($data = Input::all(), Room::$rules);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $room->update($data);

        return Redirect::action('RoomsController@getIndex');
    }

    /**
     * Remove the specified room from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function getDestroy($id)
    {
        Room::destroy($id);

        return Redirect::action('RoomsController@getIndex');
    }

}
