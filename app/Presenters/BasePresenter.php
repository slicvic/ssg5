<?php namespace App\Presenters;

use Illuminate\Database\Eloquent\Model;

/**
 * BasePresenter
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
abstract class BasePresenter {

    /**
     * The model instance.
     *
     * @var Model
     */
    protected $model;

    /**
     * Constructor.
     *
     * @param Model  $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Presents the created at datetime.
     *
     * @return string
     */
    public function createdAt()
    {
        return date('m/d/y g:i A', strtotime($this->model->created_at));
    }

    /**
     * Presents the updated at datetime.
     *
     * @return string
     */
    public function updatedAt()
    {
        return date('m/d/y g:i A', strtotime($this->model->updated_at));
    }
}
