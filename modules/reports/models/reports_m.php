<?php
class Reports_m extends CI_Model
{
    public $stores = null;
    public $errorMsg = array();
    public $currentStore = null;
    public $currentStoreProfile = null;

    public function __construct()
    {
        parent::__construct();
        //$this->load->library('files/files');
        $this->load->library('Logging');
        $this->load->driver('Streams');
        $this->load->library('Connect');
    }

    public function reportCreate($type, $code, $baseDate, $endDate)
    {
        /*
        Aloha Reports Notice:
        when you pull a report for the current day, it does not accept it as Ymd format. you must use the verbiage "DATA" and execute the report commandline with that verbiage.
        if pulling a report for some date plus the current date, you must then convert the date back to time to see how many days are within date1 and date2.
        ie: date1 is yesterday 4/9/2013, date2 is today. You must instead of using the word 'date' as the baseDate, you would use the baseDate 4/9, and then find how many days till current date
        then you would use those day differences as the /days flag with the baseDate.
        */

        $today = date('Ymd');
        if ($baseDate != 'DATA' && $endDate != 'DATA') {
            // Sort the days if they put the range backwards
            $days = (strtotime($endDate)-strtotime($baseDate));
            $days = ($days/60/60/24);
        } elseif ($baseDate != 'DATA' && $endDate == 'DATA') {
            $days = (strtotime($today)-strtotime($baseDate));
            $days = ($days/60/60/24);
        } else {
            $days = 0;
        }
        // Try to connect to SFTP and bail if it fails
        $response = $this->Store->connectSFTP();
        $stream = ssh2_exec($this->sftp->connection, 'echo %IBERDIR%');
        stream_set_blocking($stream, true);
        $iberdir = trim(stream_get_contents($stream));

        $settingFileSize = @$this->sftp->getFileSize('/RptExport/posignite.' . $type . '.exp');

        if (true !== $response) {
            die('<div class="alert error" style="width: auto;">'.$response.'</div>');
        }
        //elseif(!$settingFileSize)
        //{
        //check and see if the dir is any of the premade setting files which can only be created via aloha manager
        //Switch (strtolower($iberdir)){
        //    case 'd:\aloha':
        //                    if(is_file('addons/shared_addons/modules/reports/report_settings/aloha/d/posignite.' . $type . '.exp'))
        //                        {
        //                            $this->sftp->uploadFile($remote_file, '/RptExport/posignite.' . $type . '.exp');
        //                            die('<div class="alert error" style="width: auto;">We tried to upload the correct settings, This should only happen one time per report type at the initial installation of POSignite. Please try again. If you keep getting this message, <a href="/FAQ/#reports">Click Here to find out how to correct this</a></div>');
        //                        }
        //                    else{
        //                        die('<div class="alert error" style="width: auto;">We cannot determine the correct report setting. You probably have your POS installation located somewhere other than D:\aloha and you will have to manually create the settings within Aloha Manager. <a href="/FAQ/#reports">Click Here to find out how to correct this</a></div>');
        //                    }
        //                    break;
        //    case 'c:\aloha':
        //                    $this->sftp->uploadFile('addons/shared_addons/modules/reports/report_settings/aloha/c/posignite.' . $type . '.exp', '/RptExport/posignite.' . $type . '.exp');
        //                    break;
        //    case 'e:\aloha':
        //                    $this->sftp->uploadFile('addons/shared_addons/modules/reports/report_settings/aloha/e/posignite.' . $type . '.exp', '/RptExport/posignite.' . $type . '.exp');
        //                    break;
        //    case 'f:\aloha':
        //                    $this->sftp->uploadFile('addons/shared_addons/modules/reports/report_settings/aloha/f/posignite.' . $type . '.exp', '/RptExport/posignite.' . $type . '.exp');
        //                    break;
        //}

        //die('<div class="alert error" style="width: auto;">You have not yet created an export setting from your back of house POS management software. <a href="/FAQ/#reports">Click Here to find out how to correct this</a></div>');
        //}

        $stream = ssh2_exec($this->sftp->connection, 'echo %IBERROOT%');
        stream_set_blocking($stream, true);
        $iberroot = trim(stream_get_contents($stream));


        $iberdir_nodrive = str_replace('\\', '/', substr($iberdir, 2));

        $this->session->set_userdata('iberroot', $iberroot);
        $this->session->set_userdata('iberdir', $iberdir);
        $this->session->set_userdata('iberdir_nodrive', $iberdir_nodrive);

        ssh2_exec($this->sftp->connection, 'taskkill /F /IM rpt.exe /T');

        if ($command = ssh2_exec($this->sftp->connection, '%IBERDIR%/BIN/RPT.EXE /DATE ' . $baseDate . ' /X'.$code. ' /DAYS ' . $days . ' /load Default.' . $type . '.set /NODEPOSIT')) {
            $this->error->logMsg('SSH', 'Reports', 'Created report on POS sucessfully');
            //POS server takes little longer when it's two years. Timing isn't perfect yet.
            if ($days > 320) {
                sleep(20);
            } elseif ($days > 200) {
                sleep(20);
            } elseif ($days > 10) {
                sleep(10);
                //ssh2_exec($this->sftp->connection, 'taskkill /F /IM rpt.exe /T');
            } elseif ($days < 10) {
                sleep(5);
                //ssh2_exec($this->sftp->connection, 'taskkill /F /IM rpt.exe /T');
            }



            return true;
        } else {
            $this->error->logMsg('SSH', 'Reports', 'The report could not be generated on the POS.', true);
            return true;
        }
    }

    // Get file from SFTP
    public function reportGet($type, $code, $baseDate, $endDate)
    {
        $storeId = $this->Store->currentStore;

        if ($baseDate <= date('Ymd') && isset($storeId)) {
            if (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/'.$storeId)) {
                if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $storeId, 0755)) {
                    $this->error->logMsg('Uploads', 'Reports', 'The store directory does not exist, and was unable to be created.', true);
                }
            } elseif (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/' .$storeId.'/'.$baseDate)) {
                if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $storeId. '/' .$baseDate, 0755)) {
                    $this->error->logMsg('Uploads', 'Reports', 'The date directory for the store does not exist, and was unable to be created.', true);
                }
            } else {
                // Do nothing?
            }

            // Get report from the host
            if ($this->sftp) {
                if ($message = $this->sftp->receiveFile('/RptExport/Default.' . $type . '.csv', FCPATH.UPLOAD_PATH . 'reports/' . $storeId . '/' . $baseDate . '/' . $type . '.csv')) {
                    $this->error->logMsg('SFTP', 'Reports', 'Retrieved report from store.');
                    return array('success'=>'The report was retrieved from the store.');
                } else {
                    $this->error->logMsg('SFTP', 'Store', 'The report was not received.', true);
                    return array('error'=>'The report was not received..');
                }
            } else {
                return array('error'=>'You cannot get a date for the future.');
            }
        } else {
            echo 'could not make connection';
            unlink(FCPATH_UPLOAD_PATH . 'reports/' . $storeId . '/' . $baseDate . '/' . $type . '.csv');
            return false;
        }
    }

    public function createDir($folder, $parent = 0)
    {

            //create the first reports folder in files
        foreach (Files::folder_tree() as $row) {
            if ($row['name'] == $folder) {
                $reportDirExists = true;
            }
        }
        if (!$reportDirExists) {
            Files::create_folder($parent, $folder);
            return false;
        } else {
            return true;
        }
    }

    // Pretty sure this function is not used.
    // See reports/libraries/Aloha.php/getReport()
    public function reportShow($type = 'sales', $date1=false, $date2=false)
    {
        $today = date('Ymd');
        // Sort the days if they put the range backwards
        if (strtotime($date1) < strtotime($date2)) {
            $baseDate = $date1;
            $endDate  = $date2;
        } else {
            $baseDate = $date2;
            $endDate  = $date1;
        }

        // Bail if dates aren't set
        if (!$date1 || !$date2) {
            echo '<div class="no_data">Please pick a date range</div>';
            return false;
        }
        //date1 or date2 is greater than today
        if ($date1 > $today || $date2 > $today) {
            echo '<div class="no_data">Please pick a current date</div>';
            return false;
        } else {
            if ($date1 == $today) {
                $baseDate = 'DATA';
            }
            if ($date2 == $today) {
                $endDate == 'DATA';
            }
        }

        switch ($type) {
            case 'sales':
                $type = 'sls';
                $code = 'C';
                break;
            case 'pay':
                $type = 'pay';
                $code = 'Y';
                break;
            case 'pmix':
                $type = 'pmx';
                $code = 'P';
                break;
            case 'lbr':
                $type = 'lbr';
                $code = 'A';
                break;
            // Employee break report
            case 'ebr':
                $type = 'brk';
                $code = 'B';
                break;
            // Weekly sales
            case 'wsr':
                $type = 'wsr';
                $code = 'CW';
                break;
            case 'adp':
                $type = 'adp';
                $code = 'D';
                break;
            // Delivery driver
            case 'dd':
                $type = 'ddr';
                $code = 'DD';
                break;
            // Tip income report
            case 'tip':
                $type = 'tip';
                $code = 'J';
                break;
            // Daily hourly sales and labor
            case 'hrs':
                $type = 'hrs';
                $code = 'H';
                break;
            // Weekly hourly sales and labor
            case 'whsl':
                $type = 'whsl';
                $code = 'HW';
                break;
            case 'wpmix':
                $type = 'wpmix';
                $code = 'PQ';
                break;
            case 'foh':
                $type = 'owe';
                $code = '7';
                break;
            case 'otf':
                $type = 'otf';
                $code = '11';
                break        ;
            case 'pd':
                $type = 'pay';
                $code = 'Y';
                break;
            case 'srev':
                $type = 'rev';
                $code = 'Z';
                break;
            case 'void':
                $type = 'voi';
                $code = 'V';
                break;
            case 'od':
                $type = 'od';
                $code = 'OD';
                break;
            case 'otw':
                $type = 'otw';
                $code = 'O';
                break;
            // case 'otf':
            //	$type = 'otf';
            //	$code = '11';
            //	break;
        }

        // SFTP Report generation through command line exec
        if ($this->reportCreate($type, $code, $baseDate, $endDate)) {
            $today = date('Ymd');
            if ($baseDate == 'DATA') {
                $baseDate = $today;
            }
            if ($endDate == 'DATA') {
                $baseDate = $today;
            }
            //create the reports folder in files if it does not exit already
            //$this->createDir('Reports');
            $row = 1;
            $this->reportGet($type, $code, $baseDate, $endDate);
            echo '<table>';
            if (($handle = fopen(FCPATH . UPLOAD_PATH . 'reports/' . $this->Store->currentStore . '/' . $baseDate . '/' . $type . '.csv', "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    echo '<tr>';
                    $num = count($data);
                    for ($c=1; $c < $num; $c++) {
                        // notes:
                        // 1. if the whole line is blank leave it blank.
                        // 2. if first line is blank it is a total line at some point or title/sub title
                        if ($data[$c] == " " || $data[$c] == "" || $data[$c] == "&#32;") {
                        } else {
                            if (intval($data[$c]) || $data[$c] == "0.00 " || $data[$c] == "0.00" || $data[$c] == "0" || floatval($data[$c])) {
                                echo '<td style="text-align:center; padding: 5px;">' . $data[$c] . '</td>';
                            } else {
                                $splitData = explode(" ", $data[$c]);
                                if (isset($splitData[0])) {
                                    $dataArray[0] = substr($splitData[0], 0, 4);
                                }

                                if (isset($splitData[1])) {
                                    $dataArray[1] = substr($splitData[1], 0, 4);
                                }

                                if ($this->agent->is_mobile()) {
                                    echo '<td style="font-weight: bold; text-align:center; padding: 5px">';
                                    if (isset($dataArray[0])) {
                                        echo $dataArray[0];
                                    }
                                    echo '<br/>';
                                    if (isset($dataArray[1])) {
                                        echo $dataArray[1];
                                    }
                                    echo '</td>';
                                } else {
                                    echo '<td style="font-weight: bold; text-align:center; padding: 5px">' . $data[$c] . '</td>';
                                }
                            }
                        }
                    }
                    echo '</tr>';
                }
                fclose($handle);
            }
            echo '</table>';
        } else {
            echo '<div class="no_data">Could not create the report. <br>Perhaps a date within the specified range does not exist on the POS. Please check your logs for details.</div>';
        }
    }
}
