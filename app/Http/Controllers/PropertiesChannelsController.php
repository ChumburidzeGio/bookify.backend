<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertiesChannel;

class PropertiesChannelsController extends Controller
{

    /**
     * Display a listing of propertieschannels
     *
     * @return Response
     */
    public function getIndex()
    {
        $propertieschannels = PropertiesChannel::where('property_id', Property::getLoggedId())->get();

        return View::make('properties_channels.index', compact('propertieschannels'));
    }

    /**
     * Store a newly created propertieschannel in storage.
     *
     * @return Response
     */
    public function postStore()
    {
        $validator = Validator::make($data = Input::all(), PropertiesChannel::$rules);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        $data['property_id'] = Property::getLoggedId();
        PropertiesChannel::create($data);

        return Redirect::action('PropertiesChannelsController@getIndex');
    }

    /**
     * Display the specified propertieschannel.
     *
     * @param  int $id
     * @return Response
     */
    public function getShow($id)
    {
        $propertieschannel = PropertiesChannel::findOrFail($id);

        return View::make('properties_channels.show', compact('propertieschannel'));
    }

    /**
     * Update the specified propertieschannel in storage.
     *
     * @param  int $channelId
     * @return Response
     */
    public function postUpdate($channelId)
    {
        $validator = Validator::make($data = Input::all(), PropertiesChannel::$rules);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        PropertiesChannel::where(
            [
                'channel_id' => $channelId,
                'property_id' => Property::getLoggedId()
            ]
        )->update(
            [
                'login' => $data['login'],
                'password' => $data['password'],
                'hotel_code' => $data['hotel_code'],
            ]
        );

        return Redirect::action('PropertiesChannelsController@getIndex');
    }

    /**
     * Remove the specified propertieschannel from storage.
     *
     * @param  int $channelId
     * @return Response
     */
    public function getDestroy($channelId)
    {
        PropertiesChannel::where(
            [
                'channel_id' => $channelId,
                'property_id' => Property::getLoggedId()
            ]
        )->delete();

        return Redirect::action('PropertiesChannelsController@getIndex');
    }

}
