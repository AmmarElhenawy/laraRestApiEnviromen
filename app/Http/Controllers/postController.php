<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\posts;
use App\apiResponseTrait;
use App\Http\Resources\postResource;
use function PHPUnit\Framework\returnSelf;
use Illuminate\Support\Facades\Validator;

class postController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use apiResponseTrait;

    public function index()
    {

    $post=postResource::collection(posts::get());
    //collection for get all data
    //but in another postResource < new postResource >
    if($post){
        return $this->apiResponse($post,'ok',200);
    }
    return $this->apiResponse(null,"not found",404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'title' => 'required|unique:posts|max:255',
        'description' => 'required|max:255',
    ]);
        if ($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),404);
        }
                $post=posts::create($request->all());
                return $this->apiResponse(new postResource ($post),200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post=posts::find($id);
        if ($post) {
            return $this->apiResponse(new postResource($post),'ok',200);
        }
    return $this->apiResponse(null,"user not found",404);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
            $validator = Validator::make($request->all(), [
        'title' => 'required|unique:posts|max:255',
        'description' => 'required|max:255',
    ]);
        if ($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),404);
        }
        $post=posts::find($id);
                if (!$post) {

                    return $this->apiResponse(null,"user not found",404);
        }
        $post->update($request->all());
        if($post){
        return $this->apiResponse($post,'updated successfully',200);
    }
    return $this->apiResponse(null,"error",404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
                $post=posts::find($id);
                if (!$post) {
                    return $this->apiResponse(null,"user not found",404);
        }
        $post->delete();
        if($post){
        return $this->apiResponse($post,'deleted successfully',200);
    }
    return $this->apiResponse(null,"error",404);
    }
}
