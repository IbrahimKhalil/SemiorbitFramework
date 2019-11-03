<?php
use Semiorbit\Config\Config;

return [

    /*
    | -----------------------------------------------------------
    | DEFAULT FORM TEMPLATE
    | -----------------------------------------------------------
    */

    Config::FORMS__DEFAULT_FORM_TEMPLATE => "form",

    /*
    | -----------------------------------------------------------
    | DEFAULT FORM HONEYPOTS
    | -----------------------------------------------------------
    */

    Config::FORMS__HONEYPOTS_MAX => 3,

    Config::FORMS__HONEYPOTS_LABELS => [],

    /*
    | -----------------------------------------------------------
    | DEFAULT FORM INPUT [[NAME FORMAT]]
    | -----------------------------------------------------------
    |
    |  Input name Format string can be used to prefix or customize
    |  html input "name" or "id" [/id/ if not explicitly provided
    |  in field props].
    |
    |  Default Format = ":model_:name"
    |
    |  Keywords
    |  --------
    |  :model = Model/DataSet class name __CLASS__
    |  :name  = Field name
    |
    |  EG. Html output for name Format ":model_:name"
    |  will be as follows:
    |
    |  <input type="text" name="User_FirstName" />
    |  User = model name, FirstName = field name
    |
    |  Cautions
    |  --------
    |
    |  A. Format string should follow the pattern "/^[A-Za-z0-9_]+$/"
    |  in addition to keywords :model and :name. Otherwise the system
    |  will revert back to ":model_:name" Format.
    |
    |  B. If keyword :name is not included in Format string, it will be
    |  added to the end of the Format string anyway. so if the Format
    |  string assigned as empty string that means ":name" Format will
    |  be used eventually.
    |
    */

    Config::FORMS__INPUT_NAME_FORMAT => ":model_:name"

];