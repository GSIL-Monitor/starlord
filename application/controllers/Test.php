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
        $this->load->model('api/OssApi');

        $source = '/home/chuanhui/starlord/application/imgs/testpng.png';
        $firstNew = "/home/chuanhui/starlord/res/testpng1.png";
        $secondNew = "/home/chuanhui/starlord/res/testpng2.png";
        $thirdNew = "/home/chuanhui/starlord/res/testpng3.png";

        $firstLine = array(
            'wm_text' => '测试文字，随便写 121@#￥@：2342342-=-',
            'wm_type' => 'text',
            'wm_font_path' => '/home/chuanhui/starlord/application/ttf/songti.ttf',
            'wm_font_size' => '150',
            'wm_font_color' => 'ADFF2F',
            'wm_vrt_alignment' => 'bottom',
            'wm_hor_alignment' => 'center',
            'wm_padding' => '20',
        );

        $secondLine = array(
            'wm_text' => '测试文字，随便写 121@#￥@：2342342-=-',
            'wm_type' => 'text',
            'wm_x_transp' => 0,
            'wm_font_path' => '/home/chuanhui/starlord/application/ttf/songti.ttf',
            'wm_font_size' => '150',
            'wm_font_color' => 'ADFF2F',
            'wm_vrt_alignment' => 'center',
            'wm_hor_alignment' => 'center',
            'wm_padding' => '180',
        );

        $thirdLine = array(
            'wm_text' => '测试文字，随便写 121@#￥@：2342342-=-',
            'wm_type' => 'text',
            'wm_x_transp' => 0,
            'wm_font_path' => '/home/chuanhui/starlord/application/ttf/songti.ttf',
            'wm_font_size' => '150',
            'wm_font_color' => 'ADFF2F',
            'wm_vrt_alignment' => 'top',
            'wm_hor_alignment' => 'center',
            'wm_padding' => '340',
        );

        $this->imgHandler($source, $firstNew, $firstLine, true);
        $this->imgHandler($firstNew, $secondNew, $secondLine, true);
        $this->imgHandler($secondNew, $thirdNew, $thirdLine, true);

        $this->OssApi->uploadImg('test/111.png', $thirdNew);

        unlink($firstNew);
        unlink($secondNew);
        unlink($thirdNew);

        $this->_returnSuccess($this->OssApi->getSignedUrlForGettingObject('test/111.png'));
    }

    public function imgHandler($source, $new, $config, $output2File)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $source;
        $config['new_image'] = $new;
        $config['output_2_file'] = $output2File;

        $this->load->library('image_lib', $config);
        $this->image_lib->initialize($config);
        $this->image_lib->watermark();
        $this->image_lib->clear();
    }


    protected function _returnSuccess($data = array())
    {
        echo json_encode(array('errno' => 0, 'errmsg' => '', 'data' => $data));
        exit;
    }
}
