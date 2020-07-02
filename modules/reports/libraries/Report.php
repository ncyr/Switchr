<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Report
{
    public function __construct()
    {
        $this->_ci = &get_instance();
    }

    public function generateReport($host_id, $data)
    {
        //generate a report based on a library class name with the variable 'mod'
        if ($this->_ci->load->library('reports/aloha')) {
            if ($report = $this->_ci->$data['mod']->getReport($data, $host_id, $data['sendTo'])) {
                return $report;
            }
        }
        return false;
    }

    public function putLocalFile($hostId, $file, $dest)
    {
        if ($host->created_by == $this->_ci->current_user->id) {
            if (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/'.$hostId)) {
                if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId)) {
                    //$this->error->logMsg('Uploads', 'Reports', 'The store directory does not exist, and was unable to be created.', true);
                }
            } elseif (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/' .$hostId)) {
                if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId, 0755)) {
                    //$this->error->logMsg('Uploads', 'Reports', 'The date directory for the store does not exist, and was unable to be created.', true);
                }
            } else {
                // Do nothing?
            }
        } else {
            if ($message = $this->sftp->put("/home/$host->host_ssh_user/$dest", $file)) {
                //$this->logging->logMsg('SFTP', 'Reports', 'Retrieved report from store.');
                return array('success'=>'The file was put to the server.');
            } else {
                //$this->logging->logMsg('SFTP', 'Store', 'The report was not received.', true);
                return array('error'=>'The file was not received.');
            }
        }
    }

    public function putLocalStatement($hostId, $date)
    {
        if ($host->created_by == $this->_ci->current_user->id) {
            if (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/'.$hostId)) {
                if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId)) {
                    if (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/'.$hostId .'/'.$date)) {
                        if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId .'/'.$date)) {
                        }
                    }
                    //$this->error->logMsg('Uploads', 'Reports', 'The store directory does not exist, and was unable to be created.', true);
                }
            } elseif (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/' .$hostId)) {
                if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId, 0755)) {
                    if (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/'.$hostId .'//'.$date)) {
                        if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId .'//'.$date)) {
                        }
                    }
                    //$this->error->logMsg('Uploads', 'Reports', 'The date directory for the store does not exist, and was unable to be created.', true);
                }
            } else {
                // Do nothing?
            }
        } else {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId . '//' . $date . '//' . '//' . $_FILES['file']['name'])) {
                //$this->logging->logMsg('SFTP', 'Reports', 'Retrieved report from store.');
                return array('success'=>'The file was put to the server.');
            } else {
                //$this->logging->logMsg('SFTP', 'Store', 'The report was not received.', true);
                return array('error'=>'The file was not received.');
            }
        }
    }

    public function fileGet($hostId, $file)
    {
        if (isset($hostId)) {
            //
            ////// This is just to make sure we have this hosts home dir setup in reports uploads.
            //
            if (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/'.$hostId)) {
                if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId)) {
                    //$this->error->logMsg('Uploads', 'Reports', 'The store directory does not exist, and was unable to be created.', true);
                }
            } elseif (!is_dir(FCPATH.UPLOAD_PATH . 'reports/' . '/' .$hostId)) {
                if (!mkdir(FCPATH.UPLOAD_PATH . 'reports/' .'/'. $hostId, 0755)) {
                    //$this->error->logMsg('Uploads', 'Reports', 'The date directory for the store does not exist, and was unable to be created.', true);
                }
            } else {
                // Do nothing?
            }

            // Get report from the host
            if ($this->sftp) {
                if ($message = $this->sftp->get($file)) {
                    //$this->logging->logMsg('SFTP', 'Reports', 'Retrieved report from store.');
                    return array('success'=>'The report was retrieved from the store.');
                } else {
                    //$this->logging->logMsg('SFTP', 'Store', 'The report was not received.', true);
                    return array('error'=>'The report was not received..');
                }
            } else {
                return array('error'=>'');
            }
        } else {
        }
    }

    public function getReportSettings($host_id)
    {
        $this->_ci->load->library('reports/aloha');
        $response = $this->_ci->aloha->getReportSettings($host_id);

        if ($response[0] !== 'ssh:') {
            return $response;
        }

        return false;
    }

    public function getReportDates($host_id)
    {
        $this->_ci->load->library('reports/aloha');
        $response = $this->_ci->aloha->getDatedSubs($host_id);

        if ($response[0] !== 'ssh:') {
            return $response;
        }

        return false;
    }
    public function resetPOS($host_id)
    {
        $this->_ci->load->library('reports/aloha');
        $response = $this->_ci->aloha->refreshData($host_id);

        if ($response[0] !== 'ssh:') {
            return $response;
        }

        return false;
    }
}
