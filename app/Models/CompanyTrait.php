<?php namespace App\Models;

use Auth;

/**
 * CompanyTrait
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
trait CompanyTrait {

    /**
     * Gets the associated company.
     *
     * @return Company
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    /**
     * Filters a query by the current user's company.
     *
     * @param  Builder  $query
     * @param  int      $companyId
     * @return Builder
     */
    public function scopeMine($query)
    {
        return $query->where('company_id', Auth::user()->company_id);
    }

    /**
     * Finds a model by its id and the current user's company.
     *
     * @param  int  $id
     * @return Model|null
     */
    public static function findMine($id)
    {
        $query = static::query()->where('id', $id);

        if ( ! Auth::user()->isAdmin())
        {
            $query->where('company_id', Auth::user()->company_id);
        }

        return $query->first();
    }

    /**
     * Finds a model by its id and the current user's company and throws
     * exception if not found.
     *
     * @param  int  $id
     * @return Model|null
     * @throws ModelNotFoundException
     */
    public static function findMineOrFail($id)
    {
        $query = static::query()->where('id', $id);

        if ( ! Auth::user()->isAdmin())
        {
            $query->where('company_id', Auth::user()->company_id);
        }

        return $query->firstOrFail();
    }

    /**
     * Saves the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = array())
    {
        if (Auth::check())
        {
            if (Auth::user()->isAdmin())
            {
                if ($this->company_id)
                {
                    // Do nothing
                }
                else
                {
                    $this->company_id = Auth::user()->company_id;
                }
            }
            else
            {
                $this->company_id = Auth::user()->company_id;
            }
        }

        return parent::save($options);
    }
}
