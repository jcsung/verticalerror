#!/usr/bin/php
<?php

/*
	Name: vse.php
	By: Jeffrey Sung
	Last updated: 2013-07-22 @ 06:55
	Vertical Square Error script
	Two input parameters: (1.) experimental data file and (2.) file for comparison
	Uses linear interpolation to determine "experimental" values for comparison file data
	Then calculates and outputs sum of squares of error.

	Error checks: will stop if < 2 input parameters or if input files don't exist
	Will stop if any comparsion file data points are out of range of experiment.
	Outputs a negative number if error encountered, so that scripts can check >0
 */


function splitter($line){
        $data="";
        $count=0;
        for ($x=0; $x<strlen($line); $x++){
                $cur=substr($line,$x,1);
                if ($cur!=" "){
                        if ($count>0) $data.=" ";
                        $data.=$cur;
                        $count=0;
                }
                else{
                        $count++;
                }
        }
        return explode(" ",$data);
}

$argc=$_SERVER['argc'];
$argv=$_SERVER['argv'];

if ($argc-1<2){
	$exe=$argv[0];
	echo "-1 Syntax: $exe EXPERIMENTAL_FILE COMPARISON_FILE\n";
	die();
}

$whichfile=explode(",","Experimental data file,Comparison file");
for ($x=0; $x<2; $x++){
	if (!file_exists($argv[$x+1])){
		echo "-2 Error: ".$whichfile[$x]." not found\n";
		die();
	}
}




//read experimental data
$expt=array();
$infile=fopen($argv[1],"r");
while (true){
	$line=fgets($infile);
	if ($line===false) break;
	$line=trim($line);
	$data=splitter($line);
	array_push($expt,$data);	
}
fclose($infile);

//Sort
for ($x=0; $x<sizeOf($expt); $x++){
	for ($y=$x+1; $y<sizeOf($expt); $y++){
		if ($expt[$y][0]<$expt[$x][0]){
			$temp=$expt[$y];
			$expt[$y]=$expt[$x];
			$expt[$x]=$temp;
		}
	}
}

//echo print_r($expt,true)."\n";
//


//read in the comparison data
$comp=array();
$infile=fopen($argv[2],"r");
while (true){
	$line=fgets($infile);
	if ($line===false) break;
	$line=trim($line);
	$data=splitter($line);
	//echo $data[0]."\n";
	array_push($comp,$data);
}
fclose($infile);

//sort
for ($x=0; $x<sizeOf($comp); $x++){
	for ($y=$x+1; $y<sizeOf($comp); $y++){
		if ($comp[$y][0]<$comp[$x][0]){
			$temp=$comp[$y];
			$comp[$y]=$comp[$x];
			$comp[$x]=$temp;
		}
	}
}
/*
if ($comp[0][0]<$expt[0][0]||$comp[0][0]>$expt[sizeOf($expt)-1][0]||$comp[sizeOf($comp)-1][0]<$expt[0][0]||$comp[sizeOf($comp)-1][0]>$expt[sizeOf($expt)-1][0]){
	die("-3 Error: Data set is out of range\n");
}
 */

for ($x=0; $x<sizeOf($comp); $x++){
	if ($comp[$x][0]<$expt[0][0]||$comp[$x][0]>$expt[sizeOf($expt)-1][0]){
		echo "-3 Error: Data set is out of range: ";
		if ($comp[$x][0]<$expt[0][0]) echo $comp[$x][0].'<'.$expt[$y][0];
		if ($comp[$x][0]>$expt[sizeOf($expt)-1][0]) echo $comp[$x][0].'>'.$expt[sizeOf($expt)-1][0];
		echo "\n";
		die();
	}
}
 
//Create the experimental interpolated data
$intr=array();
for ($x=0; $x<sizeOf($comp); $x++){
	$flag=false;
	$cur=$comp[$x];
	for ($y=0; $y<sizeOf($expt); $y++){
		if ($expt[$y][0]>$cur[0]){
			$flag=true;
			break;
		}
	}
	if ($flag){
		$left=$y-1;
		$right=$y;
	}
	else{
		$left=sizeOf($expt)-2;
		$right=$left+1;
	}
	
	$m=($expt[$right][1]-$expt[$left][1])/($expt[$right][0]-$expt[$left][0]);
	$b=-$m*$expt[$left][0]+$expt[$left][1]; //in point-slope form y-y1=m(x-x1)
	$data[0]=$cur[0];
	$data[1]=$m*$cur[0]+$b;

	array_push($intr,$data);
//	echo $data[0]." ".$data[1]."\n";
}

//echo print_r($intr,true)."\n";


//calculate error
$sum=0;
$resid=array();
for ($x=0; $x<sizeOf($comp); $x++){
	$resid[$x]=pow($intr[$x][1]-$comp[$x][1],2);
	$sum+=$resid[$x];
}

echo "$sum\n";
?>
