<?php
/*
*-----------------------------------------------------------------------------------------------
* SEMIORBIT LOG       						    	  						    semiorbit.com
*-----------------------------------------------------------------------------------------------
*
*/

namespace Semiorbit\Debug;






class Log 
{
	
	private static $_LogInstance = null;
	
	//private static $_Title;
	
	private static $_LastOutput;
	
	private static $_LastOutputTxt;
	
	private static $_OutputIn = 0;
	
	private static $_Output = [];

	private static $_OutputTxt = [];
	
	//private static $_JsOutput = [];
	
	private static $_Trace = 2;
	
	private static $_TraceStartIndex = 2;
	
	
	
	private static function LogInstance()
	{
		
		if ( self::$_LogInstance  === null ) {
		
			self::$_LogInstance = new Log();
				
		}
		
		return self::$_LogInstance;
		
	}
	
	public static function Get() {	return self::$_LastOutput;	}
	
	public static function Inline() { self::$_OutputIn = 1;	return self::LogInstance(); }
	
	public static function Console() { self::$_OutputIn = 0;	return self::LogInstance(); }
	
	public static function JsConsole() { self::$_OutputIn = 2;	return self::LogInstance(); }
	
	public static function InFile($path) { self::$_OutputIn = $path; return self::LogInstance(); }
	
	public static function Trace($trace = 2) { self::$_Trace = $trace; return self::LogInstance(); }
	
	public static function TraceStartIndex($index = 2) { self::$_TraceStartIndex = $index; return self::LogInstance(); }
	
	private static function Push()
	{
		if ( self::$_OutputIn === 0 ) {
			
		} elseif ( self::$_OutputIn === 1 ){
			
			echo self::$_LastOutput;
			
		} elseif ( self::$_OutputIn === 2 ){
			
			str2console(self::$_LastOutputTxt);
			
		}
		
		if ( is_string( self::$_OutputIn ) ) {
			
		}
	}
	
	public static function Msg($title, $msg, $level="info")
	{
		$trace = debug_backtrace();
		
		ob_start();
		
		?>
		
		<div style="background-color:white;border:3px #4F5B93 solid;direction: ltr;">
		
		<dl>
			<dt><?=$title?></dt>
			<dd><pre><?php 
			switch ($level) {
				
				case "info": print_r($msg);	break;
				
				case "debug": var_dump($msg);	break;
				
				case "json": echo json_encode($msg);	break;
				
				default: echo $msg;	break;
			}
				 
			?></pre></dd>
		</dl>
		
		<?php if (self::$_Trace > 0) : ?>
		
		<a href="#" class="view_log_trace">trace</a>
		
		<?php if ( self::$_Trace + self::$_TraceStartIndex  >= self::$_TraceStartIndex -1 ) : ?>
		
		<dl class="log_trace">
		
			<dt>Started on: </dt>
			<dd><?= isset( $trace[self::$_TraceStartIndex]['class'] ) ? $trace[self::$_TraceStartIndex]['class'] : '' ?><?= isset( $trace[self::$_TraceStartIndex]['type'] ) ? $trace[self::$_TraceStartIndex]['type'] : '' ?><?= isset( $trace[self::$_TraceStartIndex]['function'] ) ? $trace[self::$_TraceStartIndex]['function'] : '' ?></dd>
			<dt>With args: </dt>
			<dd><?=json_encode($trace[self::$_TraceStartIndex]['args'])?></dd>
			<dt>File: </dt>
			<dd><?= isset( $trace[self::$_TraceStartIndex -1]['file'] ) ? $trace[self::$_TraceStartIndex -1]['file'] : '' ?> on line <?= isset( $trace[self::$_TraceStartIndex -1]['line'] ) ? $trace[self::$_TraceStartIndex -1]['line'] : '' ?></dd>
		
		</dl>
		<?php endif; ?>
			
		<?php if ( self::$_Trace + self::$_TraceStartIndex >= self::$_TraceStartIndex ) : ?>
		
		<?php for ($i = self::$_TraceStartIndex; $i <= self::$_Trace  + self::$_TraceStartIndex -1 ; $i++) : ?>
		
		<?php if ( ! isset($trace[$i+1]) ) break; ?>
		
		<dl class="log_trace">
		
			<dt>Called From: </dt>
			<dd><?=isset( $trace[$i+1]['class'] ) ? $trace[$i+1]['class'] : ''?><?= isset( $trace[$i+1]['type'] ) ? $trace[$i+1]['type'] : '' ?><?= ( isset( $trace[$i+1]['function'] ) ) ? $trace[$i+1]['function'] : ''?></dd>
			<dt>With args: </dt>
			<dd><?=json_encode($trace[$i+1]['args'])?></dd>
			<dt>File: </dt>
			<dd><?= ( isset( $trace[$i]['file'] ) ) ? $trace[$i]['file'] : '' ?> on line <?= ( isset( $trace[$i]['line'] ) ) ? $trace[$i]['line'] : ''?></dd>
			
		</dl>
		<?php endfor; ?>
		
		<?php endif; ?>
		
		
		
		<?php endif; ?>
		
		
		</div>
		
		<?php 
		
		$output = ob_get_clean();
		
		self::$_LastOutput = $output;
		
		self::$_Output[] = $output;
		
		self::TxtMsg($title, $msg, $level, $trace);
		
		return $output;
		
	}
	
	public static function TxtMsg($title, $msg, $level="info", $trace = null)
	{
		if ( is_empty( $trace ) ) $trace = debug_backtrace();
	
		ob_start();
	
		?>
			
		<?=$title?>
			
	<?php 
		
		switch ($level) {
					
			case "info":  print_r($msg);	break;
					
			case "debug": var_dump($msg);	break;
			
			case "json": echo json_encode($msg);	break;
					
			default: echo $msg;	break;
		}
					 
	?>
	<?php if (self::$_Trace > 0) : ?>
	<?php if ( self::$_Trace + self::$_TraceStartIndex >= self::$_TraceStartIndex -1 ) : ?>
				
				Started on: 
				
				<?=$trace[self::$_TraceStartIndex]['class']?><?=$trace[self::$_TraceStartIndex]['type']?><?=$trace[self::$_TraceStartIndex]['function']?>
				
				With args: 
				<?=json_encode($trace[self::$_TraceStartIndex]['args'])?>
				
				File:
				<?=$trace[self::$_TraceStartIndex -1]['file']?> on line <?=$trace[self::$_TraceStartIndex -1]['line']?>
	<?php endif; ?>
			
		<?php if ( self::$_Trace + self::$_TraceStartIndex >= self::$_TraceStartIndex ) : ?>
		<?php for ($i = 2; $i <= self::$_Trace + self::$_TraceStartIndex -1; $i++) : ?>
		<?php if ( ! isset($trace[$i+1]) ) break; ?>		
				
				Called From:

                <?=isset( $trace[$i+1]['class'] ) ? $trace[$i+1]['class'] : ''?><?= isset( $trace[$i+1]['type'] ) ? $trace[$i+1]['type'] : '' ?><?= ( isset( $trace[$i+1]['function'] ) ) ? $trace[$i+1]['function'] : ''?>
				With args:
				<?=json_encode($trace[$i+1]['args'])?>
				
				File:
                <?= ( isset( $trace[$i]['file'] ) ) ? $trace[$i]['file'] : '' ?> on line <?= ( isset( $trace[$i]['line'] ) ) ? $trace[$i]['line'] : ''?>
							
		<?php endfor; ?>
		<?php endif; ?>
	<?php endif; ?>
			<?php 
			
			$output = ob_get_clean();
			
			self::$_LastOutputTxt = $output;
			
			self::$_OutputTxt[] = $output;
			
			return $output;
			
		}
	
	
	public static function Notice($msg)
	{
		self::Msg("Notice: ", $msg, "notice");
	
		self::Push();
	
		return self::LogInstance();
	
	}
	
	public static function Info($var_name, $var_value)
	{
		self::Msg($var_name, $var_value, "info");
		
		self::Push();
		
		return self::LogInstance();
		
	}
	
	public static function Debug($var_name, $var_value)
	{
		self::Msg($var_name, $var_value, "debug");
		
		self::Push();
	
		return self::LogInstance();
	
	}
	
	public static function Json($var_name, $var_value)
	{
		self::Msg($var_name, $var_value, "json");
	
		self::Push();
	
		return self::LogInstance();
	
	}
	
	public static function Alert($msg, $title = "Alert: "/*, $mailto = ""*/)
	{
		self::Msg($title, $msg, "alert");
		
		self::Push();
	
		return self::LogInstance();
	
	}

	/**
	 *TODO:: Improve Log 
	 *
	private static function SendToJsConsole()
	{
		
	}
	
	private static function PrintInline()
	{
		
	}
	
	private function PushToConsole()
	{
		
	}
	
	private function AppendToFile()
	{
		
	}
	 */
	
	
}