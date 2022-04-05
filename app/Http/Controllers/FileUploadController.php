<?php

namespace App\Http\Controllers;

use App\Module;
use App\ModuleVersion;
use App\ModuleAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class FileUploadController extends Controller
{
    private function get_absolute_path($module,$moduleVersion) {
        return config('filesystems.disks.local.root').'/public/modules/'.$module->id.'/versions/'.$moduleVersion->id;
    }

    
    private function recursive_delete($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->recursive_delete("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function exists(Module $module,ModuleVersion $moduleVersion, Request $request)
    {
        if (file_exists($this->get_absolute_path($module,$moduleVersion))) {
            return response(['exists'=>true],200);
        } else {
            return response(['exists'=>false],200);
        }
    }
    public function upload(Module $module,ModuleVersion $moduleVersion, Request $request)
    {
        if (file_exists($this->get_absolute_path($module,$moduleVersion))) {
            if ($request->has('overwrite') && $request->get('overwrite') === 'true') {
                $this->recursive_delete($this->get_absolute_path($module,$moduleVersion));
                ModuleAssignment::where('module_version_id','=',$moduleVersion->id)->update(['current_state' => null]);
            } else {
                return response(['error'=>'File Exists'],409);
            }
        } 
        if ($moduleVersion->type === 'articulate_tincan') {
            if($request->file('zipfile')->getClientOriginalExtension()==='zip'){
                $zip = new ZipArchive();
                $res = $zip->open($request->file('zipfile'));
                if ($res === TRUE) {
                    $zip->extractTo($this->get_absolute_path($module,$moduleVersion));
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

    //Workshop Upload 

    private function get_absolute_workshop_path($workshop,$file_name) {
        dd('get_absolute_workshop_path');
        return config('filesystems.disks.local.root').'/public/workshops/'.$workshop->id.'/'.$file_name;
    }
    public function workshop_file_exists(Workshop $workshop,String $file_name, Request $request)
    {
        if (file_exists($this->get_absolute_workshop_path($workshop,$file_name))) {
            return response(['exists'=>true],200);
        } else {
            return response(['exists'=>false],200);
        }
    }
    public function workshop_file_upload(Workshop $workshop,String $file_name, Request $request)
    {
        if (file_exists($this->get_absolute_workshop_path($workshop,$file_name))) {
            if ($request->has('overwrite') && $request->get('overwrite') === 'true') {
                $this->recursive_delete($this->get_absolute_workshop_path($workshop,$file_name));
                //ModuleAssignment::where('module_version_id','=',$moduleVersion->id)->update(['current_state' => null]);
            } else {
                return response(['error'=>'File Exists'],409);
            }
        } 
        Storage::disk('public/workshops/'.$workshop->id.'/'.$file_name)->put($request->file('zipfile'), $file_name);
        echo 'ok';
        // if($request->file('zipfile')->getClientOriginalExtension()==='zip'){
        //     $zip = new ZipArchive();
        //     $res = $zip->open($request->file('zipfile'));
        //     if ($res === TRUE) {
        //         $zip->extractTo($this->get_absolute_path($module,$moduleVersion));
        //         $zip->close();
        //         echo 'ok';
        //     }
        // } else {
        //     return response(['error'=>'Must Upload Zip File for TinCan Modules'],415);
        // }
       // }


    }
}