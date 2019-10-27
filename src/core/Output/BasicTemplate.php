<?php


namespace Semiorbit\Output;


use Semiorbit\Debug\AppRuntimeException;
use Semiorbit\Support\AltaArray;
use Semiorbit\Support\Str;

class BasicTemplate
{

    protected $_Params;

    protected $_Template;

    protected $_TemplateFile;


    private const CLS_FLAG = '@@CLS@@FLG@@';

    /**
     * Basic Template syntax:
     *
     * <ul>
     *  <li><b>Params</b>: <br>
     *      {$PARAM_KEY}
     *  </li>
     *  <li><b>LOOPS</b>:
     *  <br>
     *  {LOOP $PARAM_KEY ["separator"]}<br>
     *     ... sub template section {$WITH_KEYS_FROM_ARRAY} to be looped<br>
     *     ... use {$$k} to get array key <br>
     *     ... use {$$n} to get ordered nom starting from 1 <br>
     *  {/LOOP}
     * </li>
     * <li><b>IF</b>:
     * <br>
     * {IF $PARAM_NAME} <br>
     *     ... Section to hide/show <br>
     * {/IF} <br>
     * </li>
     * </ul>
     * Nested loops and nested if sections are not supported
     *
     * @param $template
     * @param array $params
     */

    public function __construct($template = '', array $params = [])
    {

        $this->WithParams($params);

        $this->setTemplate($template);

    }

    /**
     * Loads template pattern from file
     *
     *  Basic Template syntax:
     *
     * <ul>
     *  <li><b>Params</b>: <br>
     *      {$PARAM_KEY}
     *  </li>
     *  <li><b>LOOPS</b>:
     *  <br>
     *  {LOOP $PARAM_KEY ["separator"]}<br>
     *     ... sub template section {$WITH_KEYS_FROM_ARRAY} to be looped<br>
     *     ... use {$$k} to get array key <br>
     *     ... use {$$n} to get ordered nom starting from 1 <br>
     *  {/LOOP}
     *
     * </ul>
     * Nested loops are not supported
     *
     * @param string $file_path path to tpl file
     * @return static
     */

    public static function From($file_path)
    {
        $tpl = new static();

        return $tpl->setTemplateFile($file_path);
    }

    /**
     * Sets template pattern which can contains:
     *
     * <ul>
     *  <li><b>Params</b>: <br>
     *      {$PARAM_KEY}
     *  </li>
     *  <li><b>LOOPS</b>:
     *  <br>
     *  {LOOP $PARAM_KEY ["separator"]}<br>
     *     ... sub template section {$WITH_KEYS_FROM_ARRAY} to be looped<br>
     *     ... use {$$k} to get array key <br>
     *     ... use {$$n} to get ordered nom starting from 1 <br>
     *  {/LOOP}
     *
     * </ul>
     * Nested loops are not supported
     *
     * @param $template
     * @return $this
     */

    public static function As($template)
    {
        $tpl = new static();

        return $tpl->setTemplate($template);
    }

    /**
     * List of params
     *
     * Params list will be used to render template
     * Render engine replaces each param by key name and sets its according value.
     * If param value is array, LOOP should be used in template.
     *
     * @return AltaArray
     */

    public function Params()
    {
        return $this->_Params ?: new AltaArray([]);
    }

    /**
     * Append params
     *
     * Params list will be used to render template
     * Render engine replaces each param by key name and sets its according value.
     * If param value is array, LOOP should be used in template.
     *
     * @param array $params
     * @return static
     */

    public function WithParams(array $params)
    {

        if ($this->_Params instanceof AltaArray)

            $this->_Params->Merge($params);

        else  $this->_Params = new AltaArray($params);


        return $this;

    }

    /**
     * Append a param
     *
     * Params list will be used to render template
     * Render engine replaces each param by key name and sets its according value.
     * If param value is array, LOOP should be used in template.
     *
     * @param $key
     * @param $value
     * @return $this
     */

    public function With($key, $value)
    {
        $this->_Params[$key] = $value;

        return $this;
    }

    /**
     * Sets template pattern
     *
     * For pattern syntax see BasicTemplate::As()
     * @see BasicTemplate::As()
     *
     * @param $template
     * @return $this
     */

    public function setTemplate($template)
    {
        $this->_Template = $template;

        return $this;
    }

    /**
     * Template pattern
     *
     * For pattern syntax see BasicTemplate::As()
     * @see BasicTemplate::As()
     *
     * @return $this
     */

    public function Template()
    {
        return $this->_Template;
    }

    /**
     * Loads template pattern from file
     *
     * @param string $file_path path to tpl file
     * @return static
     */

    public function setTemplateFile($file_path)
    {

        $this->_TemplateFile = $file_path;

        if (file_exists($file_path))

            $this->setTemplate(

                file_get_contents($file_path)

            );
        else

            throw new AppRuntimeException(4004, "", "Template file not found in ({$file_path})", 404);

        return $this;

    }

    /**
     * Template file
     *
     * For template syntax see BasicTemplate::From()
     * @see BasicTemplate::From()
     *
     * @return string
     */

    public function TemplateFile()
    {
        return $this->_TemplateFile;
    }


    /**
     * Template file
     *
     * For template syntax see BasicTemplate::As()
     *
     * @param bool $flush_output
     * @return string
     * @see BasicTemplate::As()
     * @see BasicTemplate::From()
     */

    public function Render($flush_output = false)
    {

        $formatted = $this->RemoveIndention($this->Template());

        $formatted = $this->FormatIf($formatted, $this->Params());

        $formatted = $this->FormatLoops($formatted, $this->Params());

        $formatted = $this->FormatString($formatted, $this->Params());

        $formatted = $this->RemoveClsFlag($formatted);

        if ($flush_output) echo $formatted;

        return $formatted;

    }


    protected function FormatString($template, $params)
    {

        return preg_replace_callback('#{\$(\w+?)\s*}#ui', function($found) use ($params){

            return strval($params[$found[1]]) ?? '';

        }, $template);

    }


    protected function FormatLoops($template, $params)
    {

        $template = preg_replace_callback('#{LOOP\s+\$(\w+?)\s*(\s"(.+)")?}(.*?){/LOOP\s*}#usi', function ($found) use ($params) {

            $list = [];

            if (! isset($params[$found[1]])) return self::CLS_FLAG;

            if (! is_array($params[$found[1]])) return strval($params[$found[1]]);

            foreach ($params[$found[1]] as $k => $datum)

                $list[] = Str::LTrimOnce((str_ireplace(['{$$k}', '{$$n}'], [$k, count($list) + 1], $this->FormatString($found[4], $datum))), PHP_EOL);

            return Str::RTrimOnce(implode($found[3], $list), PHP_EOL);

        }, $template);

        return $template;

    }

    protected function FormatIf($template, $params)
    {

        $template = preg_replace_callback('#{IF\s+\$(\w+?)\s*}(.*?){/IF\s*}#usi', function ($found) use ($params) {

            if (! isset($params[$found[1]])) return self::CLS_FLAG;

            return (boolval($params[$found[1]])) ?

                 Str::TrimOnce($found[2], PHP_EOL) : self::CLS_FLAG;

        }, $template);

        return $template;

    }


    protected function RemoveClsFlag($template)
    {

        do {


            $template = preg_replace_callback('#\\r?\\n *' . self::CLS_FLAG . ' *\\r?\\n#us', function () {

                return PHP_EOL;

            }, $template, -1,$count);

        } while ($count);


        $template = str_replace(self::CLS_FLAG, '', $template);

        return $template;

    }


    protected function RemoveIndention($template)
    {


        foreach (['{LOOP', '{/LOOP', '{IF', '{/IF'] as $keyword) {

            $template = preg_replace_callback('#\\r?\\n *'. $keyword .'#usi', function () use ($keyword) {

                return PHP_EOL . $keyword;

            }, $template);

        }



        return $template;

    }

    /**
     * @return string
     * @see BasicTemplate::Render()
     * @see BasicTemplate::From()
     * @see BasicTemplate::As()
     */

    public function __toString()
    {
        return $this->Render(false);
    }

}