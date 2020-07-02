<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Sftp
{
	public $connection;
	public $sftp;
	public $error = false;

	public function __construct($config)
	{
		$this->connection = @ssh2_connect($config['store_ssh_host'], $config['store_ssh_port']);
		if (!$this->connection)
		{
			$this->error = 'Could not connect to '.$config['store_ssh_host'].':'.$config['store_ssh_port'];
		}
	}

	public function login($username, $password)
	{
		$ci =& get_instance();
		if (!@ssh2_auth_password($this->connection, $username, $password))
		{
			$ci->error->logMsg('SFTP', 'Library', 'Could not authenticate. Username "'.$username.'", Password "'.$password.'"', true);
			throw new Exception("Could not authenticate using supplied credentials.");
		}

		$this->sftp = @ssh2_sftp($this->connection);
		if (!$this->sftp)
		{
			$ci->error->logMsg('SFTP', 'Library', 'Could not initialize SFTP subsystem.', true);
			throw new Exception("Could not initialize SFTP subsystem.");
		}
		else
		{
			$ci->error->logMsg('SFTP', 'Login', 'Connection successful');
		}
	}

	public function uploadFile($local_file, $remote_file)
	{
		$sftp = $this->sftp;
		$stream = @fopen("ssh2.sftp://$sftp$remote_file", 'w');
		if (! $stream)
		{
			throw new Exception("Could not open file: $remote_file");
		}

		$data_to_send = @file_get_contents($local_file);
		if ($data_to_send === false)
		{
			throw new Exception("Could not open local file: $local_file.");
		}
		if (@fwrite($stream, $data_to_send) === false)
		{
			throw new Exception("Could not send data from file: $local_file.");
		}
		@fclose($stream);
	}

	function scanFilesystem($remote_file) {
		$sftp = $this->sftp;
        if(!$this->sftp){
        return false;}
		$dir = "ssh2.sftp://$sftp$remote_file";
		$tempArray = array();
		$handle = opendir($dir);

		// List all the files
		while (false !== ($file = readdir($handle)))
		{
			if (substr("$file", 0, 1) != ".")
			{
				if(!is_dir($file))
				{
					$tempArray[]=$file;
				}
			}
		}
		closedir($handle);
		return $tempArray;
	}

	public function receiveFile($remote_file, $local_file)
	{
		$sftp = $this->sftp;
		$stream = @fopen("ssh2.sftp://$sftp$remote_file", 'r');

		if (! $stream)
		{
			throw new Exception("Could not open file: $remote_file");
		}

		$size = $this->getFileSize($remote_file);
		$contents = '';
		$read = 0;
		$len = $size;

		while ($read < $len && ($buf = fread($stream, $len - $read)))
		{
			$read += strlen($buf);
			$contents .= $buf;
		}

		if(file_put_contents ($local_file, $contents))
		{
			$contents = true;
		}
		@fclose($stream);

		if($contents)
		{
			return true;
		}
		return false;
	}

	public function getFileSize($file)
	{
		$sftp = $this->sftp;
		return filesize("ssh2.sftp://$sftp$file");
	}

	public function deleteFile($remote_file)
	{
		$sftp = $this->sftp;
		unlink("ssh2.sftp://$sftp$remote_file");
	}

	public function mkdir($dir, $mode)
	{
		$sftp = $this->sftp;
		$stream = fopen("ssh2.sftp://$sftp/", 'w');

		if (ssh2_sftp_mkdir($sftp, $dir, $mode))
		{
			return true;
		}
		return false;
	}
}
