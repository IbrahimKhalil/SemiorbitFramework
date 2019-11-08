<?php


namespace Semiorbit\Config\Routes;


class ActionRoute extends Route
{

    const PROPS_PATTERN = 'p';

    const PROPS_CONTROLLER = 'c';

    const PROPS_ACTION = 'a';

    const PROPS_CALLABLE = 'b';

    const PROPS_REG_PATTERN = 'r';

    const PROPS_CONSTRAINTS = 'w';

    const PROPS_PARAMS = 'i';




    public static function CompileRegPattern($pattern, $constraints)
    {

        $params = [];

        // const SEPARATORS = '/,;.:-_~+*=@|';

        // TODO: CLEAN UNSAFE CHARS FROM PATTERN

        $reg_pattern = preg_replace_callback("#{(.*?)}/?#ui", function ($matches) use($constraints, &$params) {

            if ( isset($matches[1]) ) {

                $params[] = ($param = $matches[1]);

                if (array_key_exists($param, $constraints)) {

                    return "(?P<{$param}>{$constraints[$param]})/";

                } else {

                    return "(?P<{$param}>[^/]+)/";

                }

            }

            return $matches[0];

        }, $pattern);


        return [$reg_pattern, $params];

    }


    public function Match($uri)
    {

        if ($uri === $this->RegPattern()) return [''];

        $uri = rtrim($uri, '/') . '/';

        if (preg_match("#^{$this->RegPattern()}$#ui", $uri, $params)) {

            array_shift($params);

            return $params;

        }



        return false;

    }


    public function Where(array $constraints)
    {

        [$this->_Value[self::PROPS_REG_PATTERN], $this->_Value[self::PROPS_PARAMS]] =

            static::CompileRegPattern($this->Pattern(), $constraints);

        $this->_Value[self::PROPS_CONSTRAINTS] = $constraints;

        Router::UpdateActionRoute($this);

        return $this;

    }


    /**
     * @param $pattern
     * @param $controller
     * @param $action
     * @param $callable
     * @param $reg_pattern
     * @param $constraints
     * @param $params
     * @return array
     */

    public static function Build($pattern, $controller, $action, $callable, $reg_pattern, $constraints, $params)
    {

        return [

            ActionRoute::PROPS_PATTERN => $pattern,

            ActionRoute::PROPS_CONTROLLER => $controller,

            ActionRoute::PROPS_ACTION => $action,

            ActionRoute::PROPS_CALLABLE => $callable,

            ActionRoute::PROPS_REG_PATTERN => $reg_pattern,

            ActionRoute::PROPS_CONSTRAINTS => $constraints,

            ActionRoute::PROPS_PARAMS => $params];

    }

    /**
     * @return string
     */
    public function Pattern()
    {
        return $this->_Value[self::PROPS_PATTERN];
    }

    /**
     * @return string
     */
    public function Action()
    {
        return $this->_Value[self::PROPS_ACTION];
    }

    /**
     * @return string
     */
    public function ControllerName()
    {
        return $this->_Value[self::PROPS_CONTROLLER];
    }

    /**
     * @return callable
     */
    public function Callable()
    {
        return $this->_Value[self::PROPS_CALLABLE];
    }

    /**
     * @return array
     */
    public function Constraints()
    {
        return $this->_Value[self::PROPS_CONSTRAINTS];
    }

    /**
     * @return array
     */
    public function Params()
    {
        return $this->_Value[self::PROPS_PARAMS];
    }

    /**
     * @return string
     */
    public function RegPattern()
    {
        return $this->_Value[self::PROPS_REG_PATTERN];
    }


}