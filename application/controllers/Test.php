<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

    }

    public function getAll()
    {
        $this->load->model('service/TestService');
        $this->_returnSuccess($this->TestService->getAll());
    }


    public function add()
    {
        $this->load->model('service/TestService');
        $input = $this->input->post();
        for ($i = 1; $i < 100000; $i++) {
            $route = "测试路径" . $i;
            echo $route . "\n";
            $start_loc = "(" . (string)(39.0 + mt_rand() / mt_getrandmax() * 2) . "," . (string)(115.0 + mt_rand() / mt_getrandmax() * 2) . ")";
            $end_loc = "(" . (string)(39.0 + mt_rand() / mt_getrandmax() * 2) . "," . (string)(115.0 + mt_rand() / mt_getrandmax() * 2) . ")";
            $this->TestService->add($route, $start_loc, $end_loc);
        }


        $this->_returnSuccess(null);
    }

    public function search()
    {
        $target_start = "(39.533898169423,116.99423118029)";
        $target_end = "(39.658079674774,115.95184874113)";
        $this->load->model('service/TestService');

        $this->_returnSuccess($this->TestService->search($target_start, $target_end, 100));

    }

    public function setkey()
    {
        $this->load->model('service/TestService');
        $input = $this->input->post();

        $this->_returnSuccess($this->TestService->setkey($input['a']));

    }

    public function getkey()
    {
        $this->load->model('service/TestService');

        $this->_returnSuccess($this->TestService->getkey());

    }


    public function img()
    {
        $input = $this->input->post();

        $key = $input['key'];
        $this->load->model('api/OssApi');

        $source = '/home/chuanhui/starlord/application/imgs/testpng.png';
        $new = "/home/chuanhui/starlord/res/testpng.png";
        unlink($new);

        $this->imgHandler($source, $new);

        $this->OssApi->uploadImg('test/111.png', $new);

        $this->_returnSuccess($this->OssApi->getSignedUrlForGettingObject('test/111.png'));

    }

    public function imgHandler($source, $new)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $source;
        $config['new_image'] = $new;

        $config['wm_text'] = "测试文字，随便写 121@#￥@：2342342-=-";
        $config['wm_type'] = 'text';
        $config['wm_font_path'] = '/home/chuanhui/starlord/application/ttf/songti.ttf';
        $config['wm_font_size'] = '160';
        $config['wm_font_color'] = 'ADFF2F';
        $config['wm_vrt_alignment'] = 'bottom';
        $config['wm_hor_alignment'] = 'center';
        $config['wm_padding'] = '20';

        $this->load->library('image_lib', $config);
        $this->image_lib->initialize($config);
        $this->image_lib->watermark();

    }


    protected function _returnSuccess($data = array())
    {
        echo json_encode(array('errno' => 0, 'errmsg' => '', 'data' => $data));
        exit;
    }
}
