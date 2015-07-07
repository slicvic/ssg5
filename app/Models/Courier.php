<?php namespace App\Models;

use App\Models\CompanySpecificTrait;

/**
 * Courier
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class Courier extends Base {

    use CompanySpecificTrait;

    protected $table = 'couriers';

    public static $rules = [
        'company_id' => 'required',
        'name' => 'required'
    ];

    protected $fillable = [
        'company_id',
        'name',
    ];
}
