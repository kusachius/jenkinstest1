<?php

define("WORKSPACE", "./");
define("LOGDIR", "/var/log/eadoc");
define("SRCDIR", WORKSPACE."/src");
define("DISTRDIR", WORKSPACE."/distr");
define("BUILD", "build");
define("BINDIR", "/opt/workspace/bin");

define("BUILDOWNER", "eadoc");
define("BUILDGROUP", "eadoc");

define("MAINTENANCE_USER", "tungsten");
define("MAINTENANCE_PASSWORD", "tungst3nr3p");


class Make
{

public	$target = "";
public	$version;

public function Make($argc, $argv)
{
	print_r($argv);

	//$this->version = trim(file_get_contents("version"));
	//$this->version = basename(getcwd());
	if($argc == 1) {
		echo 'version not specified';
		exit(1);
	}
	$this->version = $argv[2];
	echo $this->version."\n";

	if ($argc>2) {
		$this->target = $argv[2];
	}
}

public function run()
{
	$srcdir = SRCDIR."/".$this->version;

	switch ($this->target) {
	case "package":
		$application="eadoc";
		$build = trim(BUILD."-".$application."-".strval($this->version));
		$builddir = DISTRDIR."/".$build;
		echo "BUILD $build\n";
		echo "BUILDDIR $builddir\n";
		exec("rm -rf $builddir/*");
		exec("mkdir $builddir");
		exec("cp -rp $srcdir/* $builddir");
		$this->preproc($builddir);
		//$this->linkcollateral($builddir);
		$this->setconf("eadoc", $this->version, $builddir);
		$this->setpermissions($builddir, BUILDOWNER, BUILDGROUP);
		$this->purge($builddir);

		$distrfile = "$application-".strval($this->version).".tgz";
		exec("cd ".DISTRDIR.";".
			"rm -f $distrfile;".
			"tar -czf $distrfile  $build;".
			"chown ".BUILDOWNER.":".BUILDGROUP." $distrfile;"
		);
			//"cp $distrfile /opt/stage/src");

		//echo "scp to r@C1:/stage/upgrade\n";
		//exec("scp  ".DISTRDIR."/$application-".strval($this->version).".tgz stage@C1:/stage/upgrade");
		break;

	case "release":
		$application="eadoc";
		$build = trim(BUILD."-".$application."-".strval($this->version));
		$builddir = DISTRDIR."/".$build;
		echo "BUILD $build\n";
		echo "BUILDDIR $builddir\n";
		exec("rm -rf $builddir/*");
		exec("mkdir $builddir");
		exec("cp -rp $srcdir/* $builddir");
		$this->preproc($builddir);
		//$this->linkcollateral($builddir);
		$this->setconf("eadoc", $this->version, $builddir);
		$this->setpermissions($builddir, BUILDOWNER, BUILDGROUP);
		$this->purge($builddir);
		$this->parse($builddir);

		$distrfile = "$application-".strval($this->version).".tgz";
		exec("cd ".DISTRDIR.";".
			"rm -f $distrfile;".
			"tar -czf $distrfile  $build;".
			"chown ".BUILDOWNER.":".BUILDGROUP." $distrfile;"
		);

		break;
	default:
		$this->preproc($srcdir);
		break;
	}
}




public function copycollateral($srcdir, $builddir)
{
	echo "copycollateral($srcdir, $builddir)\n";

	exec("mv $srcdir/* $builddir");
}



public function setconf($application, $version, $builddir)
{
	echo "Setconf($application, $version)\n";

	exec("echo \"dbdcr_dsn=mysql://datacenterr:ead0c5mysql@dbdcr:3306/datacenter\" > $builddir/conf/eadoc.conf");
	exec("echo \"dbdcw_dsn=mysql://datacenterw:ead0c5mysql@dbdcw:3306/datacenter\" >> $builddir/conf/eadoc.conf");
	exec("echo \"serverid=1\" >> $builddir/conf/eadoc.conf");
	exec("echo \"clusterid=1\" >> $builddir/conf/eadoc.conf");
	exec("echo \"application=$application\" >> $builddir/conf/eadoc.conf");
	exec("echo \"version=$version\" >> $builddir/conf/eadoc.conf");
	exec("echo \"cachedir=/eadoc/cache\" >> $builddir/conf/eadoc.conf");
	exec("echo \"maintenance_user=".MAINTENANCE_USER."\" >> $builddir/conf/eadoc.conf");
	exec("echo \"maintenance_password=".MAINTENANCE_PASSWORD."\" >> $builddir/conf/eadoc.conf");	
	
}


public function preproc($builddir) 
{
	echo "preproc($builddir)\n";

	$includepath = "$builddir/ppr:$builddir/include:/usr/local/lib/php";
	$preproc = BINDIR."/preproc";
	$log = LOGDIR."/ppr.log";
	exec("cat /dev/null > $log");
	exec("find $builddir -name \"*.ppr\" -type f -exec $preproc include_path=$includepath {} ".$this->version." \; 2>> $log");
}


public function setpermissions($builddir, $owner, $group) 
{
	echo "copy($builddir, $owner, $group)\n";

	exec("chown -fR $owner:$group $builddir");
	exec("chmod 770 $builddir");	
	exec("find $builddir/* -exec touch {}  \;");
	exec("find $builddir/* -type d -exec chmod 550 {} \;");
	exec("find $builddir/* -type f -exec chmod 440 {} \;");
	exec("find $builddir/stage/cache -type d -exec chmod 770 {} \;");
	exec("find $builddir/stage/eadocinstall -exec chmod 550  {} \;");
	exec("find $builddir/conf -type d -exec chmod 770 {} \;");
	exec("find $builddir/conf/eadoc.conf -exec chmod 660 {} \;");
	exec("find $builddir/include -type d -exec chmod 770 {} \;");
	exec("find $builddir/java -type d -exec chmod 770 {} \;");
	exec("find $builddir/java/*.* -exec chmod 660 {} \;");
	exec("find $builddir/www -type d -exec chmod 770 {} \;");
	exec("find $builddir/www/*.htm -exec chmod 660 {} \;");
	exec("find $builddir/local -type d -exec chmod 770 {} \;");
	exec("find $builddir/local/* -type f -exec chmod 660 {} \;");
	exec("find $builddir/scripts/* -exec chmod 550 {} \;");
	exec("find $builddir/bin/* -exec chmod 750 {} \;");
}

public function parse($builddir)
{
	echo "Syntax Check ($builddir)\n";
	if (empty($builddir)) return;

	exec("find $builddir -name \"*.inc\" -exec  php -l {} \; > /dev/null");
}


public function purge($builddir)
{
	echo "Purge($builddir)\n";
	if (empty($builddir)) return;

	exec("find $builddir -ignore_readdir_race -name \"test\.*\" -exec rm -f {} \;");
	exec("find $builddir -ignore_readdir_race -name \"*\.ppr\" -exec rm -f {} \;");
	exec("find $builddir -ignore_readdir_race -name \"*\.bck\" -exec rm -rf {} \;");
	exec("find $builddir -ignore_readdir_race -name \"*~\" -exec rm -f {} \;");
	exec("find $builddir -ignore_readdir_race -name \"\.*\.swp\" -exec rm -f {} \;");
	exec("find $builddir -ignore_readdir_race -name \"\.svn\" -exec rm -rf {} \;");
	exec("find $builddir -ignore_readdir_race -name \"*\.old\" -exec rm -rf {} \; -a -prune");
	exec("rm -rf $builddir/www/videos");
	exec("rm -rf $builddir/www/webhelp");
	exec("rm $builddir/include/thirdparty/smarty2/templates_c/*");
	foreach (Array("small","medium","large","xsmall") as $size) {
		$file = "$builddir/www/js/eadoc-$size.js";
		echo "$file\n";
		exec("cat $file | sed -e 's/\/\/DEBUG//' | sed -e 's/debugger;//' > $file.tmp;mv $file.tmp $file");
	}
}

public function package($builddir, $distrfile, $owner, $group) 
{
	echo "Package($builddir, $distrfile, $owner, $group)\n";

}


}

$make = new Make($argc, $argv);
$make->run();


?>
