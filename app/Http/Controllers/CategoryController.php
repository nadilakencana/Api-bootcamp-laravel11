<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    // function Admin

    // get data category
    public function getCategory(){
        try{

            $category = Category::all();

            return response()->json([
                'message' => 'Data Brhasil Di ambil',
                'data' => $category
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Data Error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function CreateCategory(Request $request){

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string',
            'slug' => 'required'
        ]);

        if($validator ->fails()){
           return response()->json(['error' => $validator->errors()], 422);
        }

        try{
            $data = $request->all();
            $category = Category::create($data);

            return response()->json([
                'message' => 'Data brhasil di simpan',
                'data' => $category
            ], 200);
        }catch(\Exception $e){
              return response()->json([
                'message' => 'Data gagal di simpan cek kembali data',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function UpdateDataCategory(Request $request, $slug){

        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            $category = Category::where('slug', $slug)->update($data);
            $dataCategory = Category::find($category);
            
            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $dataCategory
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update category','data' => $e->getMessage()], 500);
        }
    }

    public function DeleteDataCategory($slug){
        try {
            $category = Category::where('slug', $slug)->delete();

            return response()->json([
                'message' => 'Category deleted successfully',
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete category','data' => $e->getMessage()], 500);
        }
    }


}
