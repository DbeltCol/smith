<?php

namespace App\Http\Controllers;

use App\Imports\ProductImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{

    public function index()
    {
        return view('encription.uploadProduct');
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('userFile') && $request->file('userFile')->isValid()) {


            $uploadedFile = $request->file('userFile');
            $filename = time() . $uploadedFile->getClientOriginalName();
            $filepath = Storage::disk('local')->putFileAs(
                'files',
                $uploadedFile,
                $filename
            );

            $filepath =  Str::replace("/files","/app/files",storage_path($filepath)); 

            if ($filepath) {

                $ext = pathinfo($filepath, PATHINFO_EXTENSION);
                $extOut = "xlsx";
                $filepathOut = Str::replace(".$ext", ".$extOut", $filepath);

                // "echo $passphrase | gpg --passphrase-fd 0 --batch --yes -o $output -d $input"

                $processCmd = $this->decrypt_command($filepath, $filepathOut, 'encriptado2022*');

                echo $processCmd;

                if (file_exists($filepathOut)) {
                    echo $filepathOut;
                    Excel::import(new ProductImport, $filepathOut);
                }
            }
        }
    }

 /*    private function processCmd($input, $output, $password)
    {

        $process = new Process(["echo", "$password", "|", "/usr/bin/gpg","--passphrase-fd", "0", "-o", "$output", "-d", "$input"]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }
 */

    function decrypt_command ($input,$output,$passphrase)
    {

        $gpg_command = "/usr/bin/gpg --homedir /home/diego/.gnupg --passphrase-fd 0 --yes --no-tty --skip-verify -o $output -d $input ";
    
        $descriptors = array(
                0 => array("pipe", "r"), //stdin
                1 => array("pipe", "w"), //stdout
                2 => array("pipe", "w"), //stderr
                );
    
        $process = proc_open($gpg_command, $descriptors, $pipes);
    
        if (is_resource($process)) {
            // send passphrase to stdin
            fwrite($pipes[0], $passphrase);
            fclose($pipes[0]);
    
            // read stdout
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
    
            // read stderr
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
    
            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $return_code = proc_close($process);
    
            $return_value = trim($stdout, "\n");
            //echo "$stdout";
    
            if (strlen($return_value) < 1) {
                $return_value = "error: $stderr";
            }
    
        }
    
        return $return_value;
    }
}
