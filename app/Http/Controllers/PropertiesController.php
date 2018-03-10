<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertiesController extends Controller
{


    /**
     * Switch current property
     * @var $id int
     */
    public function getSwitch()
    {
        Session::put(Property::PROPERTY_ID, \Illuminate\Support\Facades\Input::get('property_id'));
    }

    public function getIndex()
    {
        return Property::all();
    }

    /**
     * Store a newly created property in storage.
     */
    public function postStore(Request $request)
    {
        $this->validate($request, Property::$rules);

        return Property::create($request->all());
    }

    /**
     * Display the specified property.
     * @param  int $id
     */
    public function show($id)
    {
        return Property::findOrFail($id);
    }

    /**
     * Update the specified property in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function postUpdate($id, Request $request)
    {
        $property = Property::findOrFail($id);

        $this->validate($request, Property::$rules);

        $property->update($request->all());

        return $property;
    }

    /**
     * Remove the specified property from storage.
     *
     * @param  int $id
     */
    public function getDestroy($id)
    {
        return Property::destroy($id);
    }

}
