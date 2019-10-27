<?php


namespace Semiorbit\Config\Environment;



use Semiorbit\Config\Config;

class DotEnv
{


    protected $__Env = [];

    private $__Populate = true;




    public static function Import($path, $populate = true)
    {
        return (new static())->Load($path, $populate);
    }

    public function Load($path, $populate = true)
    {

        $this->__Populate = $populate;

        if ($data = file_get_contents($path)) {

            $data = str_replace(["\r\n", "\r"], "\n", $data);

            if ($lines = explode("\n", $data)) {

                do {

                    $this->Parse(current($lines));

                } while (false !== next($lines));

            }

        }

        return $this;

    }


    protected function Parse($line)
    {

        if ($line = trim($line)) {

            if ($line[0] === '#') return;

            [$env_name, $env_value] = explode('=', $line, 2);

            if ($this->ValidateName($name = trim($env_name))) {

                $value = $this->ExtractValue($env_value);

                if ($this->__Populate) {

                    $_ENV[$name] = $value;

                    $this->__Env[$name] = &$_ENV[$name];

                } else $this->__Env[$name] = $value;

            }

        }


        if ($this->__Populate)

            $this->PopulateKeys();

    }


    public function Populate()
    {

        foreach($this->__Env as $name => $value) {

            $_ENV[$name] = $value;

            $this->__Env[$name] = &$_ENV[$name];

        }

        $this->PopulateKeys();

        return $this;

    }

    protected function PopulateKeys()
    {

        $_ENV[Config::FWK_ENV_LIST] = isset($_ENV[Config::FWK_ENV_LIST]) ?

            array_merge($_ENV[Config::FWK_ENV_LIST], $this->__Env)

            : $this->__Env;

    }


    protected function ValidateName($env_name)
    {
        return preg_match('~\A[a-zA-Z0-9_.]+\z~', $env_name) === 1;
    }



    const STATE_INIT = 0;

    const STATE_NORMAL = 1;

    const STATE_QUOTE = 2;

    const STATE_QUOTE_CLOSE = 3;

    const STATE_ESCAPE = 4;

    const STATE_COMMENT = 5;

    protected function ExtractValue($env_value)
    {


        $i = 0;

        $result = '';

        $state = self::STATE_INIT;

        $revert_state = self::STATE_NORMAL;

        $is_quoted = false;


        foreach (str_split($env_value) as $char) {


            switch ($char) {


                case '#':

                    if ($state === self::STATE_ESCAPE || $state === self::STATE_QUOTE) $result .= '#';

                    else $state = self::STATE_COMMENT;

                    break;


                case '"':

                    if ($state === self::STATE_INIT) $state = self::STATE_QUOTE;

                    elseif ($state === self::STATE_QUOTE) $state = self::STATE_QUOTE_CLOSE;

                    else $result .= '"';

                    break;


                case '\\':

                    $next_char = $env_value[$i+1];

                    $revert_state = $state;

                    if ($next_char === '"') $state = self::STATE_ESCAPE;

                    elseif ($next_char === '#' && $state !== self::STATE_QUOTE) $state = self::STATE_ESCAPE;

                    else $result .= '\\';

                    break;


                default:

                    $result .= $char;

                    break;


            }


            if ($state === self::STATE_INIT) $state = self::STATE_NORMAL;

            elseif ($state === self::STATE_QUOTE) $is_quoted = true;

            elseif ($state === self::STATE_QUOTE_CLOSE || $state === self::STATE_COMMENT) break;

            elseif ($state === self::STATE_ESCAPE && $char !== '\\') $state = $revert_state;

            $i++;

        }


        if (!$is_quoted)

            $result = $this->EvaluateSpecialValue($result);

        return $result;

    }


    public function EvaluateSpecialValue($value)
    {

        if ($value === 'null') return null;

        elseif ($value === 'true') return true;

        elseif ($value === 'false') return false;

        else return $value;

    }

    public function List()
    {
        return $this->__Env;
    }


    public function UnPopulate()
    {

        foreach ($this->__Env as $name => $value) {

            unset($_ENV[Config::FWK_ENV_LIST][$name]);

            unset($_ENV[$name]);

        }

    }

    public static function ClearAppEnvironment()
    {

        foreach ($_ENV[Config::FWK_ENV_LIST] as $name => $value)

            unset($_ENV[$name]);


        unset($_ENV[Config::FWK_ENV_LIST]);

    }

}