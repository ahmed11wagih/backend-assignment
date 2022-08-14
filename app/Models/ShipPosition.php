<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ShipPosition extends Model
{
    protected $fillable = ['mmsi','status','stationId','speed','lon','lat','course','heading','rot','timestamp'];

    public $timestamps = false;


    public function getLonAttribute($value)
    {
        return (double)$value;
    }


    public function getLatAttribute($value)
    {
        return (double)$value;
    }


    public function setTimestampAttribute($value)
    {
        $this->attributes['timestamp'] = Carbon::parse($value)->toDateTimeString();
    }


    public function getTimestampAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }


    public function scopeWithinRange($q,$request)
    {

        $index = $request['unit'] == 'mile' ? '3959' : '6371';

        $haversine = "(".$index." * acos(cos(radians(" . $request['lat'] . "))
                    * cos(radians(`lat`))
                    * cos(radians(`lon`)
                    - radians(" . $request['lon'] . "))
                    + sin(radians(" . $request['lat'] . "))
                    * sin(radians(`lat`))))";

        return $q->select('lon','lat')->selectRaw("{$haversine} AS distance")->whereRaw("{$haversine} < ?", [$request['range']]);
    }


    public static function toCsv($data)
    {
        $filename = "ship_positions" . now()->toDateTimeString() . ".csv";
        $delimiter = ',';
        $file = fopen('php://memory', 'w');

        $fields = ['mmsi','status','stationId','speed','lon','lat','course','heading','rot','timestamp'];

        fputcsv($file, $fields, $delimiter);

        foreach($data as $row)
        {
            fputcsv($file, $row,$delimiter);
        }

        fseek($file, 0);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        fpassthru($file);

        return true;
    }
}
