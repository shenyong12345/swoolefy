<?php
/**
+----------------------------------------------------------------------
| swoolefy framework bases on swoole extension development, we can use it easily!
+----------------------------------------------------------------------
| Licensed ( https://opensource.org/licenses/MIT )
+----------------------------------------------------------------------
| Author: bingcool <bingcoolhuang@gmail.com || 2437667702@qq.com>
+----------------------------------------------------------------------
*/

namespace Swoolefy\Udp;

use Swoolefy\Core\Swoole;
use Swoolefy\Core\ServiceDispatch;
use Swoolefy\Core\HanderInterface;

class UdpHander extends Swoole implements HanderInterface {

	/**
	 * 数据分隔符
	 */
	const EOF = '::';

	/**
	 * $client_info 客户端信息
	 * @var null
	 */
	public $client_info = null;

	/**
	 * __construct 初始化
	 * @param    array  $config
	 */
	public function __construct(array $config=[]) {
		parent::__construct($config);
	}

	/**
	 * init 当执行run方法时,首先会执行init->bootstrap
	 * @param  mixed  $recv
	 * @return void       
	 */
	public function init($recv) {}

	/**
	 * bootstrap 当执行run方法时,首先会执行init->bootstrap
	 * @param  mixed  $recv
	 * @return void
	 */
	public function bootstrap($recv) {}

	/**
	 * run 完成初始化后，路由匹配和创建访问实例
	 * @param  int   $fd
	 * @param  mixed $recv
     * @throws \Throwable
	 * @return mixed
	 */
	public function run($recv, $clientInfo, array $extend_data = []) {
	    try {
            // 必须要执行父类的run方法
            parent::run($fd = null, $recv);
            $this->client_info = $clientInfo;
            // worker进程
            if ($this->isWorkerProcess()) {
                if (is_string($recv)) {
                    $packet = explode(self::EOF, $recv);
                    if (count($packet) == 3) {
                        list($service, $event, $params) = $packet;
                        if (is_string($params)) {
                            $params_arr = json_decode($params, true);
                            if (is_array($params_arr)) {
                                $params = $params_arr;
                            }
                        }
                    } else if (count($packet) == 2) {
                        list($service, $event) = $packet;
                        $params = null;
                    } else {
                        // TODO
                    }
                } else {
                    // TODO
                }

                if ($service && $event) {
                    $callable = [$service, $event];
                }
            } else {
                // 任务task进程
                $is_task_process = true;
                list($callable, $params) = $recv;
            }

            if ($callable) {
                $dispatch = new ServiceDispatch($callable, $params);
                if (isset($is_task_process) && $is_task_process === true) {
                    list($from_worker_id, $task_id, $task) = $extend_data;
                    $dispatch->setFromWorkerIdAndTaskId($from_worker_id, $task_id, $task);
                }
                $dispatch->dispatch();
            }
        }catch (\Throwable $t) {
            throw $t;

        } finally {
            // 必须执行
            if(!$this->is_defer) {
                parent::end();
            }
            return true;
        }

	}

	/**
	 * author 认证
	 * @return 
	 */
	public function author() {}
}