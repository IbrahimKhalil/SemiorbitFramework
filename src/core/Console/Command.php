<?php


namespace Semiorbit\Console;


abstract class Command
{

    private $_Cli;


    private $_Definition;

    private $_Args;


    private $_CliFile;

    private $_CliCommand;
    
    private $_Signature;


    public function __construct()
    {

        $this->_Definition = new ArgumentList();

        $this->_Args = new ArgumentList();

        $this->Configure();

    }

    /**
     * @return Cli
     */

    final public function Cli()
    {
        return $this->_Cli ?:

            $this->_Cli = new Cli();
    }

    public function UseCli(Cli $cli)
    {
        $this->_Cli = $cli;

        return $this;
    }

    /**
     * Configure command input and output. It is
     * called directly after creating command before any process
     */
    abstract public function Configure();

    /**
     * onStart event is called when running command after parsing input
     * and right before Execute function
     */
    public function onStart()
    {

    }

    public function onCliStart()
    {

    }

    public function onWebStart()
    {

    }

    /**
     * Processing input and returning results or resources to output. <br>
     * Must return results
     *
     * @return mixed $res Results or resources returned after Execute
     */
    abstract public function Execute();

    /**
     * Output for cli
     *
     * @param mixed $res Results or resources returned after Execute
     * @return mixed Output
     */
    public function CliHandle($res)
    {
        return $res;
    }

    /**
     * Output for web
     *
     * @param mixed $res Results or resources returned after Execute
     * @return mixed Output
     */
    public function WebHandle($res)
    {
        return $res;
    }


    final public function Run()
    {

        $this->ValidateDefinition();

        $this->LockDefinition();

        $this->Arguments()->UseDefinition($this->Definition());

        $this->ParseInput();


        $this->onStart();

        Console::IsCli() ?

            $this->onCliStart() : $this->onWebStart();


        $this->PushUnusedOptions();

        $this->PushUnusedFlags();


        $this->ValidateInput();


        $this->LockInput();



        $res = $this->Execute();

        $output = Console::IsCli() ?

            $this->CliHandle($res) :

            $this->WebHandle($res);

        return $output;


    }

    public function ParseInput()
    {

        
        if (Console::IsCli()) {

            global $argv;


            $input = $argv ?? [];

            $this->_CliFile = array_shift($input);

            $this->_CliCommand = array_shift($input);

            $this->ParseCliInput($input);

        } else {

            $this->ParseHttpInput( array_merge($_GET, $_POST) );

        }

    }


    public function ParseCliInput( $args )
    {


        while ( $arg = array_shift($args) ) {



            // ================================================================
            // Is it a command? (prefixed with --)
            // ================================================================

            if ( starts_with( $arg, '--') ) {


                $value = '';

                $name   = substr( $arg, 2 );


                // Is it the syntax '--option=argument'?
                // ================================================================

                if ( strpos($name,'=') )

                    [$name, $value] = explode('=', $name,2);



                // ================================================================

                $def = $this->Definition()->FindArgument($name);

                if ($def instanceof Argument) {

                    if ( $def->Type() !== Argument::TYPE_OPTION )

                        ConsoleException::InappropriateArgumentCall($def, Argument::TYPE_OPTION);

                    if ($def->IsArray() == false && $this->Arguments()->FindArgument($name, Argument::TYPE_OPTION))

                        ConsoleException::DuplicateOptionInput($def);

                }


                $this->Arguments()

                    ->AddOption($name)

                    ->UseDefinition($def)

                    ->setValue(

                    empty($value) ? true : $value

                );

                // ================================================================

                continue;

            }


            // ================================================================
            // Is it a FLAG or a SERIAL OF FLAGS? (prefixed with -)
            // ================================================================

            if ( starts_with($arg, '-') ) {

                for ($i = 1; isset($arg[$i]) ; $i++) {

                    $def = $this->Definition()->FindArgument($arg[$i]);


                    if ($def instanceof Argument && $def->Type() !== Argument::TYPE_FLAG )

                        ConsoleException::InappropriateArgumentCall($def, Argument::TYPE_FLAG);


                    $this->Arguments()

                        ->AddFlag($arg[$i])

                        ->UseDefinition($def)

                        ->setValue(true);

                }

                continue;

            }


            // ================================================================
            // Finally, it is not option, nor flag >> ARGUMENT
            // ================================================================



            // Count passed arguments

            $c = count( $this->Arguments()->ListArguments() );


            // If input is expecting another argument, add it
            // ================================================================

            if ( $name = $this->Definition()->HasArgument($c) ) {

                $def = $this->Definition()->FindArgument($name);

                $this->Arguments()->Add($name)->UseDefinition($def)->setValue($arg);



                // If last argument isArray(), append value to last argument
                // ================================================================

            } elseif ($name = $this->Definition()->HasArgument($c - 1) ) {


                $def = $this->Definition()->FindArgument($name);

                if ($def && $def->IsArray())

                    $this->Arguments()->Argument($name)->UseDefinition($def)->AddValue($arg);

                else

                    ConsoleException::UnknownArgument($arg, $this->Definition()->ListArguments());

                // Unexpected argument
                // ================================================================

            } else {

               ConsoleException::UnknownArgument($arg, $this->Definition()->ListArguments());

            }


            continue;

        }


    }





    public function ParseHttpInput( $args )
    {


    }


    public function ValidateDefinition()
    {

        $count_args = count($this->Definition()->ListArguments());

        $i = 1;



        foreach ($this->Definition()->ListArguments() as $arg) {

            /** @var Argument $arg */

            if ($i !== $count_args && $arg->IsArray())

                ConsoleException::InvalidArrayArgument($arg);


            if (! $arg->IsRequired())

                ConsoleException::InvalidRequiredArgument($arg);


            $i++;

        }


        foreach ($this->Definition()->ListFlags() as $flag) {

            /** @var Argument $flag */

            if (strlen($flag->Name()) > 1)

                ConsoleException::InvalidFlagName($flag->Name());

        }

    }


    public function ValidateInput()
    {

        // Invalid argument type

        foreach ($this->Definition() as $arg) {

            /** @var Argument $arg */

            $foundInput = $this->Arguments()->FindArgument($arg->Name());

            if ($foundInput instanceof Argument &&

                $foundInput->Type() !== $arg->Type() )


                ConsoleException::InappropriateArgumentCall($arg, $foundInput->Type());

        }

        // Missing required argument

        foreach ($this->Definition()->ListArguments() as $arg) {

            /** @var Argument $arg */

            $foundInput = $this->Arguments()->FindArgument($arg->Name(), Argument::TYPE_ARGUMENT);

            if (! $foundInput instanceof Argument)

                ConsoleException::MissingArgument($arg);


        }


        // Missing required option

        foreach ($this->Definition()->ListOptions() as $arg) {

            /** @var Argument $arg */

            if ($arg->IsRequired() && ! $this->Arguments()->FindArgument($arg->Name(), Argument::TYPE_OPTION))

                ConsoleException::MissingOption($arg);

        }


    }

    public function PushUnusedOptions()
    {

        foreach ($this->Definition()->ListOptions() as $option) {

            /** @var Argument $option */


            $usedOption = $this->Arguments()->FindArgument($option->Name());

            if (! $usedOption)

                $this->Arguments()->AddOption($option->Name())

                    ->UseDefinition($option)

                    ->setValue($option->Default());

        }

    }


    public function PushUnusedFlags()
    {

        foreach ($this->Definition()->ListFlags() as $flag) {

            /** @var Argument $flag */


            $usedFlag = $this->Arguments()->FindArgument($flag->Name());

            if (! $usedFlag)

                $this->Arguments()->AddFlag($flag->Name())->UseDefinition($flag)->setValue(false);

        }

    }

    public function LockDefinition()
    {
        $this->Definition()->setIsLocked('Definition _List');
    }

    public function LockInput()
    {
        $this->Arguments()->setIsLocked('Input Arguments _List');
    }
    
    public function Signature()
    {
        return $this->_Signature;
    }

    public function Define($signature)
    {
        $this->_Signature = $signature;
        
        $this->Definition()->ParseSignature($signature);
    }

    final public function DefineArgument($name, $arg_type = Argument::TYPE_ARGUMENT) : Argument
    {
        return $this->Definition()->Add($name, $arg_type);
    }

    final public function DefineOption($name) : Argument
    {
        return $this->Definition()->AddOption($name);
    }

    final public function DefineFlag($name) : Argument
    {
        return $this->Definition()->AddFlag($name);
    }

    final public function Definition() : ArgumentList
    {
        return $this->_Definition;
    }

    final public function Arguments() : ArgumentList
    {
       if ( ! $this->Definition()->IsLocked() )

            ConsoleException::LockedList('Input Arguments _List');

        return $this->_Args;
    }

    final public function Argument($name) : Argument
    {
        return $this->Arguments()->Argument($name);
    }

    final public function Option($name) : Argument
    {
        return $this->Arguments()->Option($name);
    }

    final public function Flag($name) : Argument
    {
        return $this->Arguments()->Flag($name);
    }


    public function CliCommand()
    {
        return $this->_CliCommand;
    }

    public function CliFile()
    {
        return $this->_CliFile;
    }

    public function ExceptionHandle(\Exception $exception)
    {

        $this->Cli()->Writeln('<mark>' . $this->Signature() . '</mark>');

        $this->Cli()->Writeln('Error: ' . $exception->getCode());

        $this->Cli()->Writeln('<error>' . $exception->getMessage() . '</error>');

    }



}