<?php
namespace App\Custom;

class JsonParser {
    private $jsonObject;
    private $pathSeparator = '.';
    private $lastValidPath = null;

    /**
     * JsonParser constructor.
     *
     * @param string $json The JSON string to parse or a file path to a JSON file.
     * @throws \Exception If the JSON string is invalid.
     */

    public function __construct(String $json, String $pathSeparator = '.') {
        try {
            $this->jsonObject = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        }catch(\JsonException $e) {
            //treat as file path
            try {
                if(!file_exists($json)) {
                    throw new \Exception('Invalid JSON string or file path.');
                }
                $this->jsonObject = json_decode(file_get_contents($json));
            }catch(\Exception $e) {
                throw new \Exception('Invalid JSON string or file path not found.');
            }
        }
        $this->pathSeparator = $pathSeparator;
    }

    /**
     * Gets the JSON object.
     *
     * @return object The JSON object.
     */
    public function getJsonObject() {
        return $this->jsonObject;
    }

    /**
     * Gets the JSON string representation of the JSON object.
     *
     * @param bool $pretty Whether to format the JSON string for readability.
     * @return string The JSON string representation of the JSON object.
     */
    public function getJsonString(bool $pretty = false) {
        return json_encode($this->jsonObject, $pretty ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Gets the value of an item in the JSON object at the specified path.
     *
     * @param string $path The path to the item in the JSON object.
     * @param mixed $default The default value to return if the item is not found.
     * @return mixed The value of the item at the specified path, or the default value if the item is not found.
     */
    public function getItem(String $path, $default = null){
        $path = explode($this->pathSeparator, $path);
        $item = $this->jsonObject;
        $this->lastValidPath = "";
        foreach($path as $key){
            if(is_object($item) && !isset($item->$key)){
                return $default;
            } elseif(is_array($item) && !isset($item[$key])) {
                return $default;
            }
            $this->lastValidPath .= $this->lastValidPath=="" ? $key : $this->pathSeparator.$key;
            $item = is_object($item) ? $item->$key : $item[$key];
        }
        return $item;
    }

    /**
     * Sets the value of an item in the JSON object at the specified path.
     *
     * @param string $path The path to the item in the JSON object.
     * @param mixed $value The value to set the item to.
     * @return void
     */
    public function setItem(String $path, $value){
        $path = explode($this->pathSeparator, $path);
        $item = $this->jsonObject;
        $lastKey = array_pop($path);
        $traversedPath = [];
        for($i=0;$i<count($path);$i++){
            $traversedPath[] = $path[$i];
            $key = $path[$i];
            $nextKey = isset($path[$i+1]) ? $path[$i+1] : $lastKey;
            if(is_array($item)){
                if($key=="[]"){
                    //get latest index
                    end($item);
                    $key = key($item)+1;
                }
                if(!isset($item[$key])){
                    $item[$key] = is_numeric($nextKey) || $nextKey=="[]" ? [] : new \stdClass();
                }
                $item = &$item[$key];
            } elseif(is_object($item)){
                if(!isset($item->$key)){
                    $item->$key = is_numeric($nextKey) || $nextKey=="[]" ? [] : new \stdClass();
                }
                $item = &$item->$key;
            }
        }
        if(is_array($item)){
            if($lastKey=="[]"){
                $item[] = $value;
            } else {
                $item[$lastKey] = $value;
            }
        } else {
            $item->$lastKey = $value;
        }
    }

    /**
     * Saves the JSON object to a file at the specified path.
     *
     * @param string $path The path to save the JSON object to.
     * @return void
     */
    public function saveAs(String $path){
        file_put_contents($path, $this->getJsonString());
    }

    /**
     * Gets the last valid path that was successfully traversed by the getItem method.
     *
     * @return string The last valid path that was successfully traversed by the getItem method.
     */
    public function getLastValidPath(){
        return $this->lastValidPath;
    }
}