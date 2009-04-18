<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
////	File:
////		select.php
////	Actions:
////		1) compile an HTML select element from an array
////	Account:
////		Added on March 23rd 2006 for ternstyle (tm) v1.0.0
////	Version:
////		4.5
////
////	Written by Matthew Praetzel. Copyright (c) 2006 Matthew Praetzel.
////////////////////////////////////////////////////////////////////////////////////////////////////

/****************************************Commence Script*******************************************/

class selectClass {
	//array, id, name, title, class, select value, selected value, javascript, multiple?
	function select($a,$i='',$n='',$t='',$c='',$sv='',$f=array(),$j='',$m=false) {
		$a = is_array($a) ? $a : array();
		foreach($a as $k => $v) {
			$s = in_array($v,$f) ? ' selected ' : '';
			$o .= '<option value="' . $v . '"' . $s . '>' . $v . '</option>';
		}
		$os = $this->compileSelect($j,$i,$n,$t,$c,$sv,$o,$m);
		return $os;
	}
	//array, id, name, title, class, select value, selected value, javascript, multiple?
	function selectPaired($a,$i='',$n='',$t='',$c='',$sv='',$f=array(),$j='',$m=false) {
		$a = is_array($a) ? $a : array();
		foreach($a as $k => $v) {
			$s = in_array($v,$f) ? ' selected ' : '';
			$k = empty($k) ? $v : $k;
			$o .= '<option value="' . $v . '"' . $s . '>' . $k . '</option>';
		}
		$os = $this->compileSelect($j,$i,$n,$t,$c,$sv,$o,$m);
		return $os;
	}
	//array, value key, value, id, name, title, class, select value, selected value, javascript, multiple?
	function selectMulti($a,$vk,$v,$i='',$n='',$t='',$c='',$sv='',$f=array(),$j='',$m=false) {
		$a = is_array($a) ? $a : array();
		$f = $this->selected($f);
		foreach($a as $k => $w) {
			$s = in_array($w[$vk],$f) ? ' selected ' : '';
			$o .= '<option value="' . $w[$vk] . '"' . $s . '>' . $w[$v] . '</option>';
		}
		$os = $this -> compileSelect($j,$i,$n,$t,$c,$sv,$o,$m);
		return $os;
	}
	//array, assoc key or value?, id, name, title, class, select value, selected value, javascript, multiple?
	function selectAssoc($a,$v,$i='',$n='',$t='',$c='',$sv='',$f=array(),$j='',$m=false) {
		$a = is_array($a) ? $a : array();
		$f = $this->selected($f);
		foreach($a as $k => $w) {
			if($v == 'key') {
				$tk = $k;
				$tv = $k;
			}
			elseif($v == 'value') {
				$tk = $w;
				$tv = $w;
			}
			//
			$s = in_array($tk,$f) ? ' selected ' : '';
			$o .= '<option value="' . $tv . '"' . $s . '>' . $tk . '</option>';
		}
		$os = $this -> compileSelect($j,$i,$n,$t,$c,$sv,$o,$m);
		return $os;
	}
	//array, value key, value, id, name, title, class, select value, selected value, javascript, multiple?
	function selectTiered($a,$vk,$v,$i='',$n='',$t='',$c='',$sv='',$f=array(),$j='',$m=false) {
		$a = is_array($a) ? $a : array();
		$f = $this->selected($f);
		$b = 0;
		foreach($a as $k => $w) {
			/*
			if($b != 0) {
				$o .= '<option value=""></option>';
			}
			*/
			$o .= '<optgroup label="'.$k.'">';
			//$o .= '<option value="">--------------------</option>';
			for($i=0;$i<count($w);$i++) {
				$s = in_array($w[$i][$vk],$f) ? ' selected ' : '';
				$o .= '<option value="' . $w[$i][$vk] . '"' . $s . '>' . $w[$i][$v] . '</option>';
			}
			$o .= '</optgroup>';
			$b++;
		}
		$os = $this -> compileSelect($j,$i,$n,$t,$c,$sv,$o,$m);
		return $os;
	}
	//array, value key, value, separator, id, name, title, class, select value, selected value, javascript, multiple?
	function selectCombined($a,$vk,$v,$p='',$i='',$n='',$t='',$c='',$sv='',$f=array(),$j='',$m=false) {
		$a = is_array($a) ? $a : array();
		$f = $this->selected($f);
		foreach($a as $l => $w) {
			for($b=0;$b<count($v);$b++) {
				$k = $v[$b];
				$key .= empty($key) ? $w[$k] : $p.$w[$k];
			}
			$s = in_array($key,$f) ? ' selected ' : '';
			$o .= '<option value="' . $w[$vk] . '"' . $s . '>' . $key . '</option>';
		}
		$os = $this -> compileSelect($j,$i,$n,$t,$c,$sv,$o,$m);
		return $os;
	}
	//start, finish, id, name, title, class, select value, selected value, javascript, multiple?
	function createNumberOptions($s,$f,$i='',$n='',$t='',$c='',$wf=array(),$j='',$m=false) {
		$wf = selected($wf);
		$b = $s;
		if($s < $f) {
			for($i=$s;$i<=$f;$i++) {
				$s = in_array($wf,$f) ? ' selected ' : '';
				$o .= '<option value="' . $i . '"' . $s . '>' . $i . '</option>';
			}
		}
		else {
			for($i=$f;$i<=$s;$i++) {
				$b = $b-1;
				$s = in_array($wf,$f) ? ' selected ' : '';
				$o .= '<option value="' . $b . '"' . $s . '>' . $b . '</option>';
			}
		}
		$os = $this -> compileSelect($j,$i,$n,$t,$c,"",$o,$m);
		return $os;
	}
	//javascript, id, name, title, class, select value, options, multiple?
	function compileSelect($j,$i,$n,$t,$c,$sv,$o,$m) {
		$j = !empty($j) ? 'onChange="' . $j . '"' : "";
		$m = $m ? ' multiple ' : '';
		//
		$sv = $sv==='' ? "select" : $sv;
		$s = $sv ? '<option value="">' . $sv . '</option>' : '';
		//
		$os = '<select ' . $j . ' name="' . $n . '" id="' . $i . '" class="' . $c . '" title="' . $t . '"' . $m . '>' . $s . $o . '</select>';
		return $os;
	}
	function selected($f) {
		return is_array($f) ? $f : explode(',',$f);
	}

}

$getOPTS = new selectClass;

/****************************************Terminate Script******************************************/
?>