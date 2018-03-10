<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Reservation
 *
 * @property integer $id
 * @property integer $property_id
 * @property integer $channel_id
 * @property string $res_id
 * @property string $res_created
 * @property string $res_inventory
 * @property string $res_plan
 * @property string $status
 * @property string $date_arrival
 * @property string $date_departure
 * @property boolean $count_adult
 * @property boolean $count_child
 * @property boolean $count_child_age
 * @property string $guest_firstname
 * @property string $guest_lastname
 * @property string $phone
 * @property string $email
 * @property string $cc_details
 * @property string $comments
 * @property float $total
 * @property string $currency
 * @property boolean $modified
 * @property string $res_source
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation wherePropertyId($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereChannelId($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereResId($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereResCreated($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereResInventory($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereResPlan($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereDateArrival($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereDateDeparture($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCountAdult($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCountChild($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCountChildAge($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereGuestFirstname($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereGuestLastname($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation wherePhone($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCcDetails($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereComments($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereTotal($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCurrency($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereModified($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereResSource($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereUpdatedAt($value)
 * @method static \Reservation getByKeys($channelId, $propertyId)
 * @property string $buyer_firstname
 * @property string $buyer_lastname
 * @property string $address
 * @property string $country
 * @property string $postal_code
 * @property string $state
 * @property float $commission
 * @property string $res_loyalty_id
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereBuyerFirstname($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereBuyerLastname($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereAddress($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCountry($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation wherePostalCode($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereState($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCommission($value)
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereResLoyaltyId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\Booking[] $bookings
 * @property float $res_cancel_fee
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereResCancelFee($value)
 * @property string $cancelled_at
 * @method static \Illuminate\Database\Query\Builder|\Reservation whereCancelledAt($value)
 */
class Reservation extends Model
{
    protected $fillable = [
        'property_id', 'channel_id', 'res_id', 'status', 'res_created', 'buyer_firstname', 'buyer_lastname',
        'email', 'phone', 'address', 'country', 'postal_code', 'state', 'comments', 'commission',
        'total', 'currency', 'cc_details', 'modified', 'res_source', 'res_loyalty_id', 'res_cancel_fee'
    ];

    /**
     * @param $query
     * @param $channelId
     * @param $propertyId
     * @return Reservation
     */
    public function scopeGetByKeys($query, $channelId, $propertyId)
    {
        return $query->where([
            'channel_id' => $channelId,
            'property_id' => $propertyId,
        ]);
    }

    public function bookings()
    {
        return $this->hasMany('Booking', 'reservation_id');
    }
}