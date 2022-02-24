<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Resources\TransactionCategoryResource;
use App\Models\TransactionCategory;

class TransactionCategoryController extends Controller
{
    /**
     * Display a listing of the transaction (income/expense) categories.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $per_page   = $request->per_page ?? 10;
        $categories = TransactionCategory::paginate($per_page);

        return response([
            'categories' => $categories,
            'message'    => 'Retrieved successfully',
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
        $data            = $request->all();
        $data['user_id'] = $user_id = $request->user()->id;
        $category_name   = $request->name;

        $validator = Validator::make($data, [
            'name' => [
                'required',
                'max:255',
                Rule::unique('transaction_categories')->where(function ($query) use ($user_id, $category_name) {
                    return $query->where('user_id', $user_id)
                        ->where('name', $category_name);
                }),
            ],
            'type' => 'required|in:income,expense',
        ]);

        if ($validator->fails()) {
            return response([
                'error'   => $validator->errors(),
                'message' => 'Validation Error',
            ], 400);
        }

        $category = TransactionCategory::create($data);

        return response([
            'category' => new TransactionCategoryResource($category),
            'message'  => 'Created successfully',
        ], 201);

    }

    /**
     * Display the specified resource.
     *
     * @param TransactionCategory $transactionCategory
     * @return Response
     */
    public function show(TransactionCategory $transactionCategory)
    {
        return response([
            'category' => new TransactionCategoryResource($transactionCategory),
            'message'  => 'Retrieved successfully',
        ]);
    }
}
