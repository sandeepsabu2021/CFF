<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Illuminate\Support\Facades\Redirect;

class VendorController extends Controller
{
    public function vendor()
    {
        $vendorData = Vendor::paginate(10);
        return view('admin.pages.vendor.vendors', ['vendorData' => $vendorData]);
    }

    public function addVendor()
    {
        $deptData = Department::all();
        return view('admin.pages.vendor.add-vendor', ['deptData' => $deptData]);
    }

    public function vendorValid(Request $req)    //add banner validation
    {
        $validateVendor = $req->validate([
            'name' => 'required|regex:/^[a-zA-Z0-9 ]{2,100}$/',
            'dept_id'  => 'required',
            'image' => 'required|mimes:jpeg,jpg,png',

        ], [
            'name.required' => "Enter title",
            'name.regex' => "Alphanumeric only, 2-100 characters",

            'dept_id.required'  => 'Department type is required',

            'image.required' => "Select image",
            'image.mimes' => "Only jpeg, jpg and png files allowed",

        ]);
        if ($validateVendor) {

            $vendor = new Vendor();
            $vendor->name = $req->name;
            $vendor->dept_id = $req->dept_id;
            $name = $req->name . '.' . $req->image->extension();
            $vendor->image = $name;
            try {
                $vendor->save();
                $req->image->move(storage_path('app/public/uploads/vendors'), $name);
                return Redirect::route('admin.vendor')->with('Success', 'vendor added successfully');
            } catch (\Illuminate\Database\QueryException $e) {
                return back()->with('Error', 'Adding vendor failed' . $e->getMessage());
            }
        }
    }

    public function editVendor($id)
    {
        $vendorData = Vendor::where('id', '=', $id)->first();
        $deptData = Department::all();
        return view('admin.pages.vendor.edit-vendor', ['vendorData' => $vendorData, 'deptData' => $deptData]);
    }



    public function editVendorValid(Request $req)    //edit banner validation
    {
        $validateVendor = $req->validate([
            'name' => 'required|regex:/^[a-zA-Z ]{2,100}$/',
            'dept_id'  => 'required',
            'image' => 'mimes:jpeg,jpg,png',
            'vid' => 'required',

        ], [
            'name.required' => "Enter title",
            'name.regex' => "Alphabets only, 2-100 characters",

            'dept_id.required'  => 'Department type is required',

            'image.mimes' => "Only jpeg, jpg and png files allowed",

        ]);
        if ($validateVendor) {

            $vendor = Vendor::where('id', '=', $req->vid)->first();
            $vendor->name = $req->name;
            $vendor->dept_id = $req->dept_id;

            try {
                $vendor->save();
                if($req->image){
                    $name = $req->img_name;
                    $req->image->move(storage_path('app/public/uploads/vendors'), $name);
                }
                return Redirect::route('admin.vendor')->with('Success', 'Vendor added successfully!');
            } catch (\Illuminate\Database\QueryException $e) {
                return back()->with('Error', 'Updating vendor failed' . $e->getMessage());
            }
        }
    }


    public function delVendor(Request $req)   //delete banner
    {
        $id = $req->id;
        $vendor = Vendor::where('id', '=', $id)->first();

        try {
            $img = storage_path('app/public/uploads/vendors/') . $vendor->image;
            unlink($img);
            $vendor->delete();
            return "Vendor deleted successfully!";
        } catch (\Illuminate\Database\QueryException $e) {
            return "Error deleting vendor - " . $e->getMessage();
        }
    }
}
