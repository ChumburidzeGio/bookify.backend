<?php

use Spatie\Activitylog\Models\Activity;

use Silber\Bouncer\BouncerFacade;

use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\SqlDBStore;
use Roomify\Bat\Store\SqlLiteDBStore;
use Roomify\Bat\Constraint\ConstraintManager;
use Roomify\Bat\Constraint\MinMaxDaysConstraint;
use Roomify\Bat\Constraint\CheckInDayConstraint;
use Roomify\Bat\Constraint\DateConstraint;
use Roomify\Bat\Test\SetupStore;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/p/index', 'PropertiesController@getIndex');
$router->get('/p/switch', 'PropertiesController@getSwitch');
$router->post('/p/store', 'PropertiesController@postStore');
$router->get('/p/show', 'PropertiesController@show');
$router->post('/p/update', 'PropertiesController@postUpdate');
$router->get('/p/destroy', 'PropertiesController@getDestroy');

//Route::controller('properties', 'PropertiesController');
//Route::controller('rooms', 'RoomsController');
//Route::controller('channels', 'PropertiesChannelsController');
//Route::controller('bulk', 'BulkController');
//Route::controller('reservations', 'ReservationsController');
//Route::controller('simulator', 'SimulatorController');

$router->get('/', function () {

    return app(\App\Channels\Expedia::class)->getInventoryList();

    app(CreateTables::class)->run();

    //Properties
    $property = app(App\Models\Property::class)->create([
        'name' => 'Hotel Tbilisi',
    ]);

    $property->attachExId('booking', 12);

    $property->exIds();

    $property->hasExId('booking', 12);

    $property->removeExId('booking');

    //User
    $user = app(App\Models\User::class)->create([
        'name' => 'Giorgi Chumburidze',
        'email' => 'chumburidze.giorgi@outlook.com',
    ]);

    $user->attachExId('facebook', 1560);

    $user->allow('manage', $property);
    $user->allow('view', $property);

    BouncerFacade::create($user)->can('manage', $property);

    //Room
    $room = $property->rooms()->create([
        'name' => 'Double room for couple',
        'price' => "15.25",
        'unit' => 'd',
        'currency' => 'USD',
        'size' => 2
    ]);

    $room->attachExId('booking', 1560);

    //Booking
    $room->newBooking($user, '2017-07-05 12:44:12', '2017-07-10 18:30:11');

    return $room;

    /*$pdo = new \PDO('sqlite::memory:');

    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    // Create tables
    $pdo->exec(SetupStore::createDayTable('availability_event', 'event'));
    $pdo->exec(SetupStore::createDayTable('availability_event', 'state'));
    $pdo->exec(SetupStore::createHourTable('availability_event', 'event'));
    $pdo->exec(SetupStore::createHourTable('availability_event', 'state'));
    $pdo->exec(SetupStore::createMinuteTable('availability_event', 'event'));
    $pdo->exec(SetupStore::createMinuteTable('availability_event', 'state'));

    $constraint = new MinMaxDaysConstraint([], 5, 8);

    $unit = new Unit(1,1, array($constraint));

    $state_store = new SqlLiteDBStore($pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-04 07:07');

    $state_event = new Event($start_date, $end_date, $unit, 1); //Event value is 0 (i.e. unavailable)

    $state_calendar = new Calendar(array($unit), $state_store);
    $state_calendar->addEvents(array($state_event), Event::BAT_HOURLY); //BAT_HOURLY denotes granularity


    $s1 = new \DateTime('2016-01-01 00:00');
    $s2 = new \DateTime('2016-01-31 12:00');

    $response = $state_calendar->getMatchingUnits($s1, $s2, array(1), array());

    dd($response);
    return Activity::all();
    activity('default')->log('Look, I logged something');
    app('translator')->setLocale('ka');
    return \App\Models\Property\PropertyType::all();
    return 'Awesomeness is coming shortly!';*/
});

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['prefix' => 'properties/'], function ($api) {
        $api->get('get', App\Http\Controllers\Property\Get::class);
        $api->get('getTypes', App\Http\Controllers\Property\GetTypes::class);
        $api->post('create', App\Http\Controllers\Property\Create::class);
    });

});