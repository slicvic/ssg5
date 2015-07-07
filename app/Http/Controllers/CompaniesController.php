<?php namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Validator;

use App\Models\Company;
use App\Models\Address;
use App\Helpers\Flash;

/**
 * CompaniesController
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class CompaniesController extends BaseAuthController {

    public function __construct(Guard $auth)
    {
        parent::__construct($auth);
        $this->middleware('admin');
    }

    /**
     * Shows a list of companies.
     */
    public function getIndex()
    {
        $companies = Company::all();
        return view('companies.index', ['companies' => $companies]);
    }

    /**
     * Shows the form for creating a new company.
     */
    public function getCreate()
    {
        return view('companies.form', ['company' => new Company()]);
    }

    /**
     * Creates a new company.
     */
    public function postStore(Request $request)
    {
        $input = $request->all();

        // Validate input
        $validator = Validator::make($input, Company::$rules);

        if ($validator->fails()) {
            Flash::error($validator);
            return redirect()->back()->withInput();
        }

        // Create address
        $address = Address::create([]);

        // Create company
        $company = new Company($input);
        $company->address()->associate($address);
        $company->save();

        Flash::success('Company created.');
        return redirect('companies');
    }

    /**
     * Shows the form for editing a company.
     */
    public function getEdit($id)
    {
        $company = Company::findOrFail($id);
        return view('companies.form', ['company' => $company]);
    }

    /**
     * Updates a specific company.
     */
    public function postUpdate(Request $request, $id)
    {
        $input = $request->all();

        // Validate input
        $validator = Validator::make($input, Company::$rules);

        if ($validator->fails()) {
            Flash::error($validator);
            return redirect()->back()->withInput();
        }

        // Update company
        $company = Company::findOrFail($id);
        $company->update($input);

        Flash::success('Company updated.');
        return redirect()->back();
    }
}
