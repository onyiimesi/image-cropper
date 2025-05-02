<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageCropController extends Controller
{
    public function index()
    {
        return view('image-crop');
    }

    public function uploadCropped(Request $request)
    {
        $request->validate([
            'cropped_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('cropped_image')) {
            $path = $request->file('cropped_image')->store('cropped', 'public');
            $url = Storage::url($path);
            return response()->json(['path' => $url]);
        }

        return response()->json(['error' => 'No image uploaded'], 400);
    }
}
