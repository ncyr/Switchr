<?php

class ConfigEncoder {

    public function load()
    {
        $key = $this->toChars(array(19, 188, 45, 68, 168, 81, 180, 228, 71, 38, 227, 1, 103, 192, 46, 94));
        $this->key = $key.substr($key, 0, 24 - strlen($key));

        $this->vector = $this->toChars(array(47, 101, 72, 77, 97, 116, 77, 202));

        return $this;
    }
    public function fromFile($filename = '')
    {
        $this->data = file_get_contents($filename);

        return $this;
    }

    public function fromString($str = '')
    {
        $this->data = $str;

        return $this;
    }

    private function pad($input)
    {
        $text_add = strlen($input) % 8;

        for ($i = $text_add; $i < 8; $i++)
        {
            $input .= chr(8 - $text_add);
        }

        return $input;
    }

    private function toChars(array $ints)
    {
        $str = '';

        foreach ($ints as $int)
        {
            $str .= chr($int);
        }

        return $str;
    }

    public function encrypt()
    {
        $this->mcryptOpen();
        $this->data = mcrypt_generic($this->td, $this->pad($this->data));
        $this->mcryptClose();

        return $this;
    }
    public function decrypt($data_encrypted)
    {
         /////************************** decrypt from base64 string ******************************************
        //invert base64
        $datano64=base64_decode($data_encrypted);

        //init encryption
        $td = mcrypt_module_open (MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        $key =array(19,188,45,68,168,81,180,228,71,38,227,1,103,192,46,94);
            $key2='';
           	for ($i=0;$i<count($key);$i++)
        		$key2 .=chr($key[$i]);
            $key=$key2;
        //convert vector to string
            $iv=array(47, 101, 72, 77, 97, 116, 77, 202);
            $iv2='';
                for ($i=0;$i<count($iv);$i++)
                        $iv2 .=chr($iv[$i]);
            $iv=$iv2;

        //ini encryption
            $td = mcrypt_module_open (MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');

            // Complete the key (because of length)
            $key_add = 24-strlen($key);
            $key .= substr($key,0,$key_add);

        	//init encrypt
            mcrypt_generic_init ($td, $key, $iv);
            //encrypt data
            $decoded = mdecrypt_generic ($td, $datano64);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        return $decoded;
    }

    private function mcryptOpen()
    {
        $this->td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init ($this->td, $this->key, $this->vector);
    }

    private function mcryptClose()
    {
        mcrypt_generic_deinit($this->td);
        mcrypt_module_close($this->td);
    }

    public function asBase64()
    {
        return base64_encode($this->data);
    }
    public function fromBase64()
    {
        return base64_decode($this->data);
    }
    public function __toString()
    {
        return $this->data;
    }
}
