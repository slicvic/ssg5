<?php namespace App\Http\Controllers;

use Validator;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use App\Models\PackageType;

/**
 * PackageTypesController
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class PackageTypesController extends BaseAuthController {

    /**
     * Constructor.
     *
     * @param  Guard $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        parent::__construct($auth);

        $this->middleware('auth.adminOrHigher');
    }

    /**
     * Shows a list of package types.
     *
     * @return Response
     */
    public function getIndex()
    {
        $types = PackageType::all();

        return view('admin.package_types.index', ['types' => $types]);
    }

    /**
     * Shows the form for creating a new package type.
     *
     * @return Response
     */
    public function getCreate()
    {
        return view('admin.package_types.create', ['type' => new PackageType]);
    }

    /**
     * Creates a new package type.
     *
     * @param  Request  $request
     * @return Redirector
     */
    public function postStore(Request $request)
    {
        $input = $request->all();

        $this->validate($input, PackageType::$rules);

        PackageType::create($input);

        return $this->redirectWithSuccess('package-types', 'Package type created.');
    }

    /**
     * Shows the form for editing a package type.
     *
     * @param  int  $id
     * @return Response
     */
    public function getEdit($id)
    {
        $type = PackageType::findOrFail($id);

        return view('admin.package_types.edit', ['type' => $type]);
    }

    /**
     * Updates a specific package type.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return Redirector
     */
    public function postUpdate(Request $request, $id)
    {
        $input = $request->only('name');

        $this->validate($input, PackageType::$rules);

        PackageType::findOrFail($id)->update($input);

        return $this->redirectBackWithSuccess('Package type updated.');
    }

    /**
     * Deletes a specific package type.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return Redirector
     */
    public function getDelete(Request $request, $id)
    {
        if ( ! PackageType::findOrFail($id)->delete())
        {
            return $this->redirectBackWithError('Package type deletion failed.');
        }

        return $this->redirectBackWithSuccess('Package type deleted.');
    }

    /**
     * Returns all package types formatted for a jquery x-editable select field.
     *
     * @return JsonResponse
     * @uses   Ajax
     */
    public function getEditableOptions()
    {
        $types = PackageType::all();

        $json = [];

        foreach ($types as $type)
        {
            $json[] = ['value' => $type->id, 'text' => $type->name];
        }

        return response()->json($json);
    }
}
