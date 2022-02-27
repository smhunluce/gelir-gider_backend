<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transaction (income/expense).
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $per_page     = $request->per_page ?? 10;
        $transactions = Transaction::with('category')
            ->where('user_id', $request->user()->id);

        // category filter
        $request->whenHas('category_id', function ($c) use ($transactions) {
            $categories = is_array($c) ? $c : [$c];
            $categories = array_filter($categories, function ($v) {
                if (!is_numeric($v)) return FALSE;
                if (!ctype_digit((string)$v)) return FALSE;

                return TRUE;
            });

            if (!empty($categories)) {
                $transactions->whereIn('category_id', array_map('intval', $categories));
            }
        });

        // amount filter
        if ($request->filled('min_amount') && $request->filled('max_amount')) {
            $amount_1 = str_replace(',', '.', $request->min_amount);
            $amount_2 = str_replace(',', '.', $request->max_amount);
            if (is_numeric($amount_1) && is_numeric($amount_2)) {
                $min_amount = min([$amount_1, $amount_2]);
                $max_amount = max([$amount_1, $amount_2]);

                $transactions->whereBetween('amount', [(double)$min_amount, (double)$max_amount]);
            }
        } elseif ($request->filled('min_amount')) {
            $min_amount = str_replace(',', '.', $request->min_amount);
            if (is_numeric($min_amount)) {
                $transactions->where('amount', '>=', (double)$min_amount);
            }
        } elseif ($request->filled('max_amount')) {
            $max_amount = str_replace(',', '.', $request->max_amount);
            if (is_numeric($max_amount)) {
                $transactions->where('amount', '<=', (double)$max_amount);
            }
        }

        // currency filter
        $request->whenHas('currency', function ($c) use ($transactions) {
            $currencies = is_array($c) ? $c : [$c];
            $currencies = array_filter($currencies);
            if (!empty($currencies)) {
                $transactions->whereIn('currency', $currencies);
            }
        });

        // transadtion date range filter
        if ($request->filled('from_date') || $request->filled('to_date')) {
            try {
                $from_date = Carbon::createFromFormat('d.m.Y', $request->from_date)
                    ->format('Y-m-d');
            } catch (InvalidFormatException $e) {
            }

            try {
                $to_date = Carbon::createFromFormat('d.m.Y', $request->to_date)
                    ->format('Y-m-d');
            } catch (InvalidFormatException $e) {
            }

            if (isset($from_date) && isset($to_date)) {
                $min_date = min([$from_date, $to_date]);
                $max_date = max([$from_date, $to_date]);
                $transactions->whereBetween('transaction_date', [$min_date, $max_date]);
            } elseif (isset($from_date)) {
                $transactions->where('transaction_date', '>=', $from_date);
            } elseif (isset($to_date)) {
                $transactions->where('transaction_date', '<=', $to_date);
            }
        }

        // description filter
        $request->whenFilled('description', function ($c) use ($transactions) {
            $transactions->where('description', 'like', '%' . $c . '%');
        });

        // if valid order request is received
        $request->whenHas('order_by', function ($order_by) use ($transactions) {
            if (!is_array($order_by))
                $order_by = [$order_by => 'ASC'];

            foreach ($order_by as $field => $dir) {
                if (in_array(strtolower($field), ['transaction_date', 'amount'])) {
                    $dir = in_array(strtolower($dir), ['asc', 'desc']) ? strtolower($dir) : 'asc';
                    $transactions->orderBy(strtolower($field), strtolower($dir));
                }
            }
        });
        if (!$transactions->getQuery()->orders) {
            $transactions->orderBy('id', 'desc');
        }

        return response([
            'transactions' => $transactions->paginate($per_page),
            'message'      => 'Retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data             = $request->all();
        $data['user_id']  = $user_id = $request->user()->id;
        $data['amount']   = str_replace(',', '.', $request->amount);
        $data['currency'] = strtoupper($request->currency);
        $request->merge([
            'amount'   => $data['amount'],
            'currency' => $data['currency'],
        ]);

        $validator = Validator::make($data, [
            'category_id'      => [
                'required',
                Rule::exists('transaction_categories', 'id')->where(function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                }),
            ],
            'amount'           => 'required|numeric|gt:0',
            'currency'         => 'required|in:TRY,USD,EUR',
            'transaction_date' => 'required|date_format:d.m.Y',
        ]);

        if ($validator->fails()) {
            return response([
                'error'   => $validator->errors(),
                'message' => 'Validation Error',
            ], 400);
        }

        $data['transaction_date'] = Carbon::createFromFormat('d.m.Y', $request->transaction_date)
            ->format('Y-m-d');

        $transaction = Transaction::create($data);

        return response([
            'transaction' => new TransactionResource($transaction),
            'message'     => 'Created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Transaction $transaction
     * @return Response
     */
    public function show(Transaction $transaction)
    {
        return response([
            'transaction' => new TransactionResource($transaction),
            'message'     => 'Retrieved successfully'],
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Transaction $transaction
     * @return Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        $data             = $request->all();
        $data['user_id']  = $user_id = $request->user()->id;
        $data['amount']   = str_replace(',', '.', $request->amount);
        $data['currency'] = strtoupper($request->currency);
        $request->merge([
            'amount'   => $data['amount'],
            'currency' => $data['currency'],
        ]);

        $validator = Validator::make($data, [
            'user_id'          => 'required|integer|size:' . $transaction->user_id,
            'category_id'      => [
                'required',
                Rule::exists('transaction_categories', 'id')->where(function ($query) use ($user_id) {
                    return $query->where('user_id', $user_id);
                }),
            ],
            'amount'           => 'required|numeric|gt:0',
            'currency'         => 'required|in:TRY,USD,EUR',
            'transaction_date' => 'required|date_format:d.m.Y',
        ]);


        if ($validator->fails()) {
            return $validator->errors()->hasAny('user_id')
                ? response(['message' => 'Unauthorized'], 403)
                : response([
                    'error'   => $validator->errors(),
                    'message' => 'Validation Error',
                ], 400);
        }

        $data['transaction_date'] = Carbon::createFromFormat('d.m.Y', $request->transaction_date)
            ->format('Y-m-d');
        $transaction->update($data);

        return response([
            'transaction' => new TransactionResource($transaction),
            'message'     => 'Retrieved successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Transaction $transaction
     * @return Response
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return response(['message' => 'Deleted']);
    }
}
