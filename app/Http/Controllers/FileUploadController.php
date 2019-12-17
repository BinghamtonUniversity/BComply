<?php

namespace App\Http\Controllers;

use App\Module;
use App\ModuleVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FileUploadController extends Controller
{
    //
    public function upload(Request $request,Module $module,ModuleVersion $moduleVersion)
    {
        if ($moduleVersion->type === 'articulate_tincan') {
            if($request->file('zipfile')->getClientOriginalExtension()==='zip'){
                $zip = new ZipArchive();
                $res = $zip->open($request->file('zipfile'));
                if ($res === TRUE) {
                    $zip->extractTo('storage/modules/' . $module->id . '/versions/' . $moduleVersion->id);
                    $zip->close();
                    echo 'ok';
               }
            } else {
                return response(['error'=>'Must Upload Zip File for TinCan Modules'],415);
            }
        }
        else {
            return response(['error'=>'This Module Type does not support File Uploads'],415);
        }

    }
}
