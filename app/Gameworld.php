<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Null_;
use Psy\Exception\BreakException;

class Gameworld extends Model
{
    protected $table = 'gameworld';
    protected $fillable = ['composite_key_fail', 'xCord', 'yCord', 'city_id'];


    public static function addCity($xCord, $yCord, $cityInfo = [], $tribe)
    {
        $square = DB::table('gameworld')->where([
            ['xCord', '=', $xCord],
            ['yCord', '=', $yCord],
        ])->whereNull('city_id')->get()->first();
        $id = City::createCity($cityInfo, $tribe);
        if ($square != null) { // Square not occupied possible to add.
            $square->city_id = $id;
            Gameworld::where('composite_key_fail', '=', $square->composite_key_fail)
                ->update(['city_id' => $id]);

        } else { // square occupied taking random square.
            $square = Gameworld::inRandomOrder()->where([
                ['city_id', '=', null]
            ])->get()->first();

            if ($square === null) // map might be full
            {
                throw new BreakException();
            }
            Gameworld::where('composite_key_fail', '=', $square->composite_key_fail)
                ->update(['city_id' => $id]);
        }
        return $id;
    }

    public static function getCity($xCord, $yCord)
    {
        return DB::table('gameworld')->where([
            ['xCord', '=', $xCord],
            ['yCord', '=', $yCord]
        ])->whereNotNull('city_id');
    }
    public static function clearUsersCities($user_id)
    {
        $cities = City::getPlayersCities($user_id);
        foreach ($cities as $city) {
            self::where('city_id', '=', $city->id)
                ->update(['city_id' => null]);
             City::destroy($city->id);
        }
    }
}
