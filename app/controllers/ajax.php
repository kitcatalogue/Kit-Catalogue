<?php
/**
 * Actions methods need not send the reply object,
 * as it will be sent automatically in ->afterAction()
 */
class Controller_Ajax extends Ecl_Mvc_Controller {
    // Public Properties
    public $reply = null;
    /**
     * --------------------------------------------------------------------------------
     * Public Methods
     */
     public function beforeAction()

    {
         $this -> router() -> layout(null);
         $this -> response() -> setHeader('Content-Type', 'application/json');
         $this -> reply = Ecl :: factory('Ecl_Ajax_Reply');
         $this -> reply -> setupFromRequest($this -> request());
         } // /method
     public function afterAction()

    {
         echo($this -> reply -> toJson());
         } // /method
     public function actionIndex()

    {
         $this -> reply -> setOk();
         $this -> reply -> setData('AJAX API entry point.');
         } // /method
     public function actionCpvmatch()

    {
         $this -> reply -> setOk();
         $query = $this -> request() -> get('q');
         $matches = $this -> model('cpvstore') -> findMatches($query);
         if (count($matches) > 0) {
            $this -> reply -> setData('matches', $matches -> toCustomArray(function ($row)

                    {
                         $x = new stdClass();
                         $x -> id = $row -> id;
                         $x -> name = $row -> name;
                         return $x;
                         }
                    ));
             }
        } // /method
     public function actionFindou()

    {
         if (!$this -> model('security') -> checkAuth(KC__AUTH_CANADMIN)) {
            $this -> reply -> setFail('Access denied');
             return;
             }
        $ou_id = $this -> request() -> get('id');
         $ou = $this -> model('organisationalunitstore') -> find($ou_id);
         if (!$ou) {
            $this -> reply -> setFail('Unknown OU requested.');
             return;
             } else {
            if (0 == $ou -> tree_level) {
                $this -> reply -> setFail('Access denied to root OU.');
                 } else {
                $this -> reply -> setData('ou', $ou);
                 }
            }
        } // /method
     public function actionGetNameSuggestions()

    {
         // TODO: Change to Regular user?
        if (!$this -> model('security') -> checkAuth(KC__AUTH_CANADMIN)) {
            $this -> reply -> setFail('Access denied');
             return;
             }
        $query = $this -> request() -> get('q');
         $results = $this -> model('userstore') -> findPartialMatch($query);
         // die(var_dump($results));
        $this -> reply -> SetData('result', $results);
         } // /method
     public function actionUploadImageFiles()

    {
         // Check if Authenticated as Admin:
       if (!$this -> model('security') -> checkAuth(KC__AUTH_CANADMIN)) {
            $this -> reply -> setFail('Access denied');
             return;
             }  // */
        $id = $this -> request() -> get('id');
        if (!is_numeric($id)) {
            $this -> reply -> SetFail('INVALID item ID!');
             return;
             }
        $i = 1;
        //build array of file urls:
        while ($this -> request() -> get('file_' . $i)) {
            $files[$i-1]['url'] = $this -> request() -> get('file_' . $i);
             $i++;
             }
        // check if files were passed as array
        if ($this -> request() -> get('files')){
        $tmps = $this -> request() -> get('files');
          for ($j = 0; $j < count($tmps); $j++){
            $files[$j]['url'] = $tmps[$j];
            }
        }
        $item = $this -> model('itemstore') -> find($id);
        $item_path = $this -> model() -> get('app.upload_root') . '/items' . $item -> getFilePath();
         // create directory if it doesn't exist:
        if (!is_dir($item_path)) {
            mkdir($item_path, 0755, true);
             }
        if (!isset($files)){
        $this -> reply -> SetData('success', 'no-change');
        return;
        }
        foreach($files as $file) {
            // first download the file to folder $item_path:
            $original_name = basename($file['url']);
             if (file_put_contents($item_path . '/' . $original_name, file_get_contents($file['url']))) {
                $file['filename'] = $original_name;
                $file['path'] = $item_path . '/' . $original_name;
                $new_file = $this -> model('itemstore') -> newItemFile();
                $filename = basename($file['path']);
                $extension = strtolower(Ecl_Helper_Filesystem :: getFileExtension($filename));
                $new_file -> item_id = $item -> id;
                $new_file -> filename = $filename;
                $new_file -> type = 0;
                $new_file -> name = '';
                $this -> model('itemstore') -> setFileInfo($new_file);
                 } else {
                   $this -> reply -> SetFail('File Copy failed for:' . $file['url']);
                   return;
                 }
            }
        // Check selected image settings
        // !! code below copied from modules/admin/controllers/item.php:
        $files = $this -> model('itemstore') -> findFilesForItem($item);
         $image_files = array();
         if (!empty($files)) {
            $image_ext = array ('jpg', 'jpeg', 'gif', 'png');
             foreach($files as $file) {
                $file_path = "{$item_path}/{$file->filename}";
                 if (file_exists($file_path)) {
                    $extension = strtolower(Ecl_Helper_Filesystem :: getFileExtension($file -> filename));
                     if (in_array($extension, $image_ext)) {
                        if ((null !== $this -> model('item.image.max_width')) || (null !== $this -> model('item.image.max_height'))) {
                            $img = Ecl_Image :: createFromFile($file_path);
                             if ($img) {
                                $img -> resizeWithinLimits($this -> model('item.image.max_width'), $this -> model('item.image.max_height'));
                                 $img -> save();
                                 }
                            }
                        $image_files[] = $file;
                         }
                    }
                } // /foreach(file)
             }


        $image_count = count($image_files);
         if (0 == $image_count) {
            $item -> image = '';
             } elseif (1 == $image_count) {
            $item -> image = $image_files[0] -> filename;
             } else {
            $item -> image = $this -> request() -> post('use_image', '');
             }

        // If the item's main image is still blank, but there are images available, use the first one
        if (('' == $item -> image) && ($image_count > 0)) {
            $item -> image = $image_files[0] -> filename;
             }
        $this -> model('itemstore') -> update($item);
        // End of copied code here!
        $this -> reply -> SetData('success', 'ok');
         } // /method
     } // /class
?>