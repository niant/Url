<?php
namespace Apok\Component\Url;

class Url
{
    /**
     * Redirect to HTTP address
     *
     * @param   string $url Url to redirect to
     */
    public static function redirect($url)
    {
        header("HTTP/1.1 302 Found");
        header('Location: '.$url);
        exit();
    }

    /**
     * Get hostname URL
     *
     * @return  string Hostname URL (without request URI)
     */
    public static function getCurrentHostnameUrl()
    {
        $host = 'localhost';

        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) {
            $url = 'https';
        } else {
            $url = 'http';
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }

        $url .= '://'.$host;

        return $url;
    }

    /**
     * Get current URL in which browser currently is
     *
     * @return  string URL where browser currently is
     */
    public static function getCurrentUrl()
    {
        $url = self::getCurrentHostnameUrl().$_SERVER['REQUEST_URI'];

        return $url;
    }

    /**
     * Build url with old/new parameters
     *
     * @param   string $url Url to generate as a basis, default current url (OPTIONAL)
     * @param   array $newParams New parameters to url, default NULL (OPTIONAL)
     * @param   array $removeParams Removable parameters from the url, default NULL (OPTIONAL)
     * @param   bool $urlEncodeParams Enable url encode on paramaters, default false (OPTIONAL)
     * @return  string $url New url with the right parameters
     */
    public static function generateUrl($url=null, array $newParams=null,
        array $removeParams=null, $urlEncodeParams=false)
    {
        if (!$url) {
            $url = self::getCurrentUrl();
        }

        $queryString    = null;
        $currentParams  = array();
        $params         = array();
        $queryString    = strrchr($url, '?');

        if (isset($queryString)) {
            $url = str_replace($queryString, '', $url);
            $queryString = substr($queryString, 1); //remove the ? mark
            parse_str($queryString, $currentParams);
        }

        // old params and new params set
        if (is_array($currentParams) && count($currentParams)>0 &&
            is_array($newParams) && count($newParams)>0) {
            $params = array_merge($currentParams, $newParams);
        } // only new params set
        else if (is_array($newParams) && count($newParams)>0) {
            $params = $newParams;
        } // only old params set
        else if (is_array($currentParams) && count($currentParams)>0) {
            $params = $currentParams;
        }

        // Remove empty and removable parameters
        if (is_array($params) && count($params)>=1) {
            foreach ($params as $key => $value) {
                if ($value=='' || (is_array($removeParams) && array_key_exists($key, $removeParams))) {
                    unset($params[$key]);
                }
            }
        }

        // format the url with parameters
        if (is_array($params) && count($params)>=1) {

            if ($urlEncodeParams) {
                $url = $url.'?'.urlencode(http_build_query($params));
            } else {
                $url = $url.'?'.http_build_query($params);
            }
        }

        return $url;
    }

    /**
     * Make a curl request
     *
     * @param   string $url URL to call
     * @param   bool $returnBoolean Return only status of request(successful, not successful), Defaul=false (OPTIONAL)
     * @return  mixed Response data or boolean depending on given parameters
     */
    public static function request($url, $returnBoolean=false, array $postData=null)
    {
        $response = null;
        $handle   = curl_init();

        curl_setopt($handle, CURLOPT_URL, $url);
        if ($returnBoolean) {
            curl_setopt($handle, CURLOPT_HEADER, true);
        }
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($handle, CURLOPT_MAXREDIRS, 5);
        // curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);

        if (is_array($postData) && count($postData)>0) {
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $postData);
        }

        $response = curl_exec($handle);

        if ($returnBoolean) {
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

            if ($httpCode==200) {
                $response = true;
            }
        }

        curl_close($handle);

        return $response;
    }
}
