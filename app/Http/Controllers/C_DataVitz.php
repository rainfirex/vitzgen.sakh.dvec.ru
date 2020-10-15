<?php

namespace App\Http\Controllers;

use App\Modules\ExportCSV;
use App\Modules\StopwatchScript;
use App\Modules\UploadFiles;
use App\Modules\Vitz;
use Illuminate\Http\Request;

class C_DataVitz extends Controller
{
    public function view() {
        return view('view');
    }

    /**
     * Загрузить файл
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFile(Request $request) {
        $uploadFile = $request->file('file');
        $errorCode = null;

        $name = $uploadFile->getClientOriginalName();

        if ($name === 'abonents.txt'){
            $result = true;
        }
        elseif(preg_match('/^SB[01-9]+_[a-zA-Z01-9]+/i',$name, $matches)) {
            $result = true;
        }
        elseif (preg_match('/^E[01-9]+_[a-zA-Z01-9]+/i', $name, $matches)) {
            $result = true;
        }
        else
            $result = false;

        if (!$result)
            return response()->json(['result'=>$result, 'filename'=> $name, 'message'=>'Доступ запрещен']);

        $limitUpload =  ini_get('upload_max_filesize');
        $errorCode = 0;
        $errorMessage = [
            0=>"Ошибок не возникло, файл был успешно загружен на сервер.",
            1=>"Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini.",
            2=>"Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме.",
            3=>"Загружаемый файл был получен только частично.",
            4=>"Файл не был загружен.",
            6=>"Отсутствует временная папка.",
            7=>"Не удалось записать файл на диск."
        ];


        if($uploadFile->isValid()) {
            $result = UploadFiles::upload($uploadFile);
            $filename = UploadFiles::resultFileName();
        } else {
            $result = false;
            $filename = null;
            $errorCode = $uploadFile->getError();
        }

//        $result = Storage::disk('local')->putFileAs(
//            UploadFiles::DIRECTORY.$filename,
//            $uploadFile,
//            $filename
//        );

        return response()->json(['result' => $result, 'filename'=> $filename, 'error_code'=>$errorMessage[$errorCode], 'limit_upload'=>$limitUpload]);
    }

    /**
     * Получить все файлы
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFiles() {
        $files = [];
        if (file_exists(UploadFiles::DIRECTORY)) {
            $files = array_diff(scandir(UploadFiles::DIRECTORY, 1), ['..', '.', 'abonents.txt']);

            // Сортировка по созданию
            usort($files, function ($a, $b) {
                return filemtime(UploadFiles::DIRECTORY . $a) < filemtime(UploadFiles::DIRECTORY . $b);
            });
        }

        return response()->json([
            'dir'=> asset(UploadFiles::DIRECTORY),
            'files' => $files
        ],200);
    }

    /**
     * Удалить файл
     * @param string $filename
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFile(string $filename) {
        if ($filename !== null && file_exists(UploadFiles::DIRECTORY.$filename)) {
            $result = unlink(UploadFiles::DIRECTORY.$filename);
            return response()->json(['result' => $result, 'message'=>'Файл удален!', 'file'=>$filename]);
        }
        return response()->json(['result' => false, 'message'=>'Файл не найден.']);
    }

    /**
     * Сформировать файл
     * @param string $filename
     * @param string $typeReport
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(string $filename, string $typeReport){

        $response = [];

        if ($filename === null && !file_exists(UploadFiles::DIRECTORY.$filename))
            return response()->json(['result' => false, 'message'=>'Ошибка чтения оригинального файла: Файл не найден.']);

        StopwatchScript::start();
        ini_set('memory_limit', '1024m');

        $vitz = Vitz::init($filename, $typeReport);
        $endString = $vitz->equal()->old()->endString();
        $subscribers = $vitz->subscribers();
        $unknowns = $vitz->unknowns();
        $mail = $vitz->getMailArray();

        $prefixFound = '';
        $prefixOld = 'old_';

        if (!empty($subscribers)) {
            $csv = new ExportCSV($prefixFound.$filename);
            $csv->save($subscribers)->writeLine($endString)->encoding();

            $response['countSubscribers'] = count($subscribers);
            $response['urlSubscribers'] = asset(ExportCSV::DIRECTORY . $prefixFound.$filename);
            $response['filenameSubscribers'] = $prefixFound.$filename;
        }

        if (!empty($unknowns)) {
            $csv = new ExportCSV($prefixOld.$filename);
            $csv->save($unknowns)->encoding();

            $response['countUnknowns'] = count($unknowns);
            $response['urlUnknowns'] = asset(ExportCSV::DIRECTORY . $prefixOld.$filename);
            $response['filenameUnknowns'] = $prefixOld.$filename;
        }

        if (!empty($mail)) {

            $i = 1;
            $count = count($mail);
            $mailFilename = $vitz->getMailFilename();

            $res = fopen(ExportCSV::DIRECTORY.$mailFilename, 'w+');
            foreach ($mail as $value) {
                if (!empty($value)) {
                    if ($i === $count) {
                        fwrite($res, $value);
                    } else {
                        fwrite($res, $value.PHP_EOL);
                    }

                    $i++;
                }
            }
            fclose($res);

            $response['countMails'] = count($mail);
            $response['urlMails'] = asset(ExportCSV::DIRECTORY . $mailFilename);
            $response['filenameMails'] = $mailFilename;
        }

        $response['result'] = true;
        $response['message'] = 'Формирование завершено!!!';
        $response['timerWork'] = StopwatchScript::toString('');
        $response['error'] = $vitz->getError();

        return response()->json($response, 200);
    }
}
