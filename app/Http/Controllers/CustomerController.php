<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Customer",
 *     type="object",
 *     title="Customer",
 *     description="Customer Model",
 *     required={"first_name", "last_name", "email"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
 *     @OA\Property(property="age", type="integer", example=25),
 *     @OA\Property(property="dob", type="string", format="date", example="1998-05-21")
 * )
 */

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/customers",
     *     summary="Get list of customers",
     *     tags={"Customers"},
     *     @OA\Response(
     *         response=200,
     *         description="List of customers",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Customer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Customer::query();
        if ($search = $request->input('search.value')) {
            $query->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }
       // Get total records count (before pagination)
        $totalRecords = Customer::count();

        // Paginate based on DataTables request
        $customers = $query->paginate($request->input('length', 10));

        return response()->json([
            'draw' => intval($request->input('draw')),  // DataTables draw count
            'recordsTotal' => $totalRecords,  // Total records in database
            'recordsFiltered' => $customers->total(), // Records after filtering
            'data' => $customers->items(),  // Paginated data
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/customers",
     *     summary="Create a new customer",
     *     description="Creates a new customer record",
     *     tags={"Customers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "age", "dob", "email"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="age", type="integer", example=30),
     *             @OA\Property(property="dob", type="string", format="date", example="1994-05-12"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Customer created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Customer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'age' => 'required|integer|min:1|max:120',
            'dob' => 'required|date',
            'email' => 'required|email|max:100|unique:customers,email',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        $customer = Customer::create($request->all());

        return response()->json(['message' => 'Customer created successfully', 'data' => $customer], 201);
    }
    
    /**
     * @OA\Get(
     *     path="/api/customers/{id}",
     *     summary="Get customer details",
     *     description="Retrieve details of a single customer by ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Customer ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer details retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/Customer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer not found")
     *         )
     *     )
     * )
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $customer]);
    }

    /**
     * @OA\Put(
     *     path="/api/customers/{id}",
     *     summary="Update customer details",
     *     description="Update customer details by ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Customer ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Jane"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="age", type="integer", example=32),
     *             @OA\Property(property="dob", type="string", format="date", example="1992-07-25"),
     *             @OA\Property(property="email", type="string", example="jane.doe@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Customer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer not found")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'age' => 'sometimes|integer|min:1|max:120',
            'dob' => 'sometimes|date',
            'email' => 'sometimes|email|max:100|unique:customers,email,' . $customer->id,
        ]);

        $customer->update($request->all());

        return response()->json(['message' => 'Customer updated successfully', 'data' => $customer]);
    }
    
    /**
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     summary="Delete customer",
     *     description="Delete a customer by ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Customer ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer not found")
     *         )
     *     )
     * )
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully']);
    }

    public function viewForm(){

        return view('customer_form');
    }

    
}
