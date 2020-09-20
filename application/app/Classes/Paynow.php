<?php

namespace App\Classes;

class Paynow
{

    public function CreateHash($values, $MerchantKey)
    {
        $string = "";
        foreach ($values as $key => $value) {
            if (strtoupper($key) != "HASH") {
                $string .= $value;
            }
        }
        $string .= $MerchantKey;

        $hash = hash("sha512", $string);
        return strtoupper($hash);
    }

    public function UrlIfy($fields)
    {
        //url-ify the data for the POST
        $delim = "";
        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= $delim . $key . '=' . $value;
            $delim = "&";
        }

        return $fields_string;
    }

    public function CreateMsg($values, $MerchantKey)
    {
        $fields = array();
        foreach ($values as $key => $value) {
            $fields[$key] = urlencode($value);
        }

        $fields["hash"] = urlencode($this->CreateHash($values, $MerchantKey));

        $fields_string = $this->UrlIfy($fields);
        return $fields_string;
    }

    public function ParseMsg($msg)
    {
        //convert to array data
        $parts = explode("&", $msg);
        $result = array();
        foreach ($parts as $i => $value) {
            $bits = explode("=", $value, 2);
            $result[$bits[0]] = urldecode($bits[1]);
        }

        return $result;
    }

    public function write_php_ini($array, $file)
    {
        $res = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $res[] = "[$key]";
                foreach ($val as $skey => $sval) $res[] = "$skey = " . (is_numeric($sval) ? $sval : '"' . $sval . '"');
            } else $res[] = "$key = " . (is_numeric($val) ? $val : '"' . $val . '"');
        }
        $this->safefilerewrite($file, implode("\r\n", $res));
    }

    public function safefilerewrite($fileName, $dataToSave)
    {
        if ($fp = fopen($fileName, 'w')) {
            $startTime = microtime(TRUE);
            do {
                $canWrite = flock($fp, LOCK_EX);
                // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
                if (!$canWrite) usleep(round(rand(0, 100) * 1000));
            } while ((!$canWrite) and ((microtime(TRUE) - $startTime) < 5));

            //file was locked so now we can store information
            if ($canWrite) {
                fwrite($fp, $dataToSave);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }

    }
}
