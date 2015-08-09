<?php namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Log;
use Exception;

use App\Models\Account;
use App\Models\Address;
use App\Http\Requests\AccountFormRequest;

/**
 * General Purpose Accounts Controller
 *
 * @author Victor Lantigua <vmlantigua@gmail.com>
 */
class AccountsController extends BaseAuthController {

    /**
     * Constructor.
     *
     * @param  Guard $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        parent::__construct($auth);

        $this->middleware('auth.agentOrHigher');
    }

    /**
     * Shows a list of customer accounts.
     *
     * @param  Request  $request
     * @return Response
     */
    public function getIndex(Request $request)
    {
        $params['limit'] = 10;
        $params['sort'] = $request->input('sort', 'id');
        $params['order'] = $request->input('order', 'desc');
        $params['search'] = $request->input('search');
        $params['type'] = $request->input('type', 'all');

        $criteria['search'] = $params['search'];
        $criteria['company_id'] = $this->user->isAdmin() ? NULL : $this->user->company_id;
        $criteria['type'] = $params['type'];

        $accounts = Account::search($criteria, $params['sort'], $params['order'], $params['limit']);

        return view('admin.accounts.index', [
            'accounts' => $accounts,
            'params' => $params
        ]);
    }

    /**
     * Shows the form for creating a new customer account.
     *
     * @return Response
     */
    public function getCreate()
    {
        return view('admin.accounts.create', [
            'account' => new Account,
            'address' => new Address
        ]);
    }

    /**
     * Creates a new customer account.
     *
     * @param  Request  $request
     * @return Redirector
     */
    public function postStore(AccountFormRequest $request)
    {
        $input = $request->all();

        // Create account
        $account = new Account;
        $account->name = $input['name'];
        $account->email = $input['email'];
        $account->phone = $input['phone'];
        $account->fax = $input['fax'];
        $account->mobile_phone = $input['mobile_phone'];

        // Create address
        $address = new Address;
        $address->address1 = $input['address1'];
        $address->address2 = $input['address2'];
        $address->city = $input['city'];
        $address->state = $input['state'];
        $address->postal_code = $input['postal_code'];
        $address->country_id = $input['country_id'];
        $address->save();

        // Associate account and address
        $account->shippingAddress()
            ->associate($address)
            ->save();

        if ( ! empty($input['tag_ids']))
        {
            $account->tags()->attach($input['tag_ids']);
        }

        return $this->redirectWithSuccess('accounts', 'Customer created.');
    }

    /**
     * Shows the form for editing an customer account.
     *
     * @param  int  $id
     * @return Response
     */
    public function getEdit($id)
    {
        $account = Account::findMineOrFail($id);

        return view('admin.accounts.edit', [
            'account' => $account,
            'address' => $account->shippingAddress ?: new Address
        ]);
    }

    /**
     * Updates a specific customer account.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return Redirector
     */
    public function postUpdate(AccountFormRequest $request, $id)
    {
        $input = $request->all();

        // Update account
        $account = Account::findMineOrFail($id);
        $account->name = $input['name'];
        $account->email = $input['email'];
        $account->phone = $input['phone'];
        $account->fax = $input['fax'];
        $account->mobile_phone = $input['mobile_phone'];

        // Update address
        $address = ($account->shippingAddress) ?: new Address;
        $address->address1 = $input['address1'];
        $address->address2 = $input['address2'];
        $address->city = $input['city'];
        $address->state = $input['state'];
        $address->postal_code = $input['postal_code'];
        $address->country_id = $input['country_id'];
        $address->save();

        // Associate account and address
        $account->shippingAddress()
            ->associate($address)
            ->save();

        // Update account tags
        $account->tags()->sync($input['tag_ids']);

        return $this->redirectBackWithSuccess('Customer updated.');
    }

    /**
     * Deletes the specified account.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return Redirector
     */
    public function getDelete(Request $request, $id)
    {
        $model = Account::findMineOrFail($id);

        try
        {
            if ($model->user_id)
            {
                throw new Exception('Integrity constraint violation.');
            }

            $model->delete();

            return $this->redirectBackWithSuccess('Account deleted.');
        }
        catch(Exception $e)
        {
            Log::error($e);

            return $this->redirectBackWithError(trans('messages.error_delete_constraint', ['name' => 'account']));
        }
    }

    /**
     * Retrieves customer or shipper accounts for an ajax autocomplete field.
     *
     * @param  Request  $request
     * @return JsonResponse
     * @uses   Ajax
     */
    public function getAutocompleteSearch(Request $request)
    {
        $input = $request->only('term');

        // Return nothing if search term is less than 2 characters long.
        if (strlen($input['term']) < 2)
        {
            return response()->json([]);
        }

        $accounts = Account::autocompleteSearch($input['term'])
            ->mine()
            ->limit(25)
            ->get();

        $json = [];

        foreach($accounts as $account)
        {
            $json[] = [
                'id'    => $account->id,
                'label' => $account->name,
                'email' => $account->email,
                'address' => $account->present()->address()
            ];
        }

        return response()->json($json);
    }
}
