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

include_once SWOOLEFY_CORE_ROOT_PATH.'/MainEventInterface.php';

use Swoolefy\Core\UdpEventInterface;

abstract class UdpEventServer extends UdpServer implements UdpEventInterface {
	/**
	 * __construct 初始化
	 * @param array $config
	 */
	public function __construct(array $config=[]) {
		parent::__construct($config);
	}

    /**
     * onWorkerStart
     * @param $server
     * @param $worker_id
     * @return mixed
     */
	public abstract function onWorkerStart($server, $worker_id);

	/**
	 * onPack 
	 * @param    object $server
	 * @param    mixed $data
	 * @param    array $clientInfo
	 * @return   void
	 */
	public function onPack($server, $data, $clientInfo) {
        $app_conf = \Swoolefy\Core\Swfy::getAppConf();
        $appInstance = new \Swoolefy\Udp\UdpHander($app_conf);
        $appInstance->run($data, $clientInfo);
	}

	/**
	 * onTask 
	 * @param    object  $server
	 * @param    int     $task_id
	 * @param    int     $from_worker_id
	 * @param    mixed   $data
     * @param    mixed   $task
	 * @return   boolean
	 */
	public function onTask($server, $task_id, $from_worker_id, $data, $task = null) {
		list($callable, $taskData, $clientInfo) = $data;
        $app_conf = \Swoolefy\Core\Swfy::getAppConf();
        $appInstance = new \Swoolefy\Udp\UdpHander($app_conf);
        $appInstance->run([$callable, $taskData], $clientInfo, [$from_worker_id, $task_id, $task]);
		return true;
	}

	public abstract function onFinish($server, $task_id, $data);

	/**
	 * onPipeMessage 
	 * @param    object  $server
	 * @param    int     $from_worker_id
	 * @param    mixed   $message
	 * @return   void
	 */
	public abstract function onPipeMessage($server, $from_worker_id, $message);

}
