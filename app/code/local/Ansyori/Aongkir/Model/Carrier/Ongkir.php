<?php
/**
 * Indonesia Shipping Carriers
 * @copyright   Copyright (c) 2015 Ansyori B.
 * @email		ansyori@gmail.com / ansyori@kemanaservices.com
 * @build_date  March 2015   
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */  
class Ansyori_Aongkir_Model_Carrier_Ongkir     
		extends Mage_Shipping_Model_Carrier_Abstract
		implements Mage_Shipping_Model_Carrier_Interface
	{  
        protected $_code = 'ongkir';  
        public function collectRates(Mage_Shipping_Model_Rate_Request $request){  
		  	if (!$this->getConfigFlag('active')) {
            	return false;
        	}
		
			$list_euy = $this->getListRates();
		
            $result = Mage::getModel('shipping/rate_result');  
			$count = 0;
			foreach($list_euy as $jangar_kana_hulu)
			{
				$count++;
				//getMethodRates($code,$title,$name,$rates)
				$method = $this->getMethodRates('_'.$count,'',$jangar_kana_hulu['text'],$jangar_kana_hulu['cost']);
				$result->append($method);
			}
			
			
           
			  
            return $result;  
        }
		
		public function helper($type = 'aongkir')
		{
			return Mage::helper($type);	
		}
		
		public function getCityId()
		{
			$string_city = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getCity();
			
			
			$sql = "select * from daftar_alamat where concat(type,' ',city_name) = '$string_city' limit 0,1 ";
			
			$res =  $this->helper()->fetchSql($sql);
			
			return $res[0]['city_id'];
			
		}
		
		public function getListRates()
		{
			$origin = $this->getOriginId();
			$dest = $this->getCityId();
			$weight = $this->getBeratTotal();
			$carriers = $this->getActiveCarriers();
			
			$rate_list = array();
			//getRates($origin,$dest,$weight,$kurir)
			foreach($carriers as $kurir)
			{
				$rates_by_kurir = $this->helper()->getRates($origin,$dest,$weight,$kurir);
				foreach($rates_by_kurir as $final_list)
				{
					$rate_list[] = array(
						'text' => $final_list['text'],
						'cost' => $final_list['cost'],
						
					);
				}
			}
			$this->helper()->setLog('Final rate '.print_r($rate_list,true));
			return $rate_list;
			
		}
		
		public function getActiveCarriers()
		{
			return explode(',',strtolower($this->helper()->config('kurir')));
		}
		
		public function getOriginId()
		{
			return $this->helper()->config('origin');
		}
		
		public function getBeratTotal()
		{
			$items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
			$totalWeight = 0;
			foreach($items as $item) {
				$totalWeight += ($item->getWeight() * $item->getQty()) ;
			}
			
			return $totalWeight;
		}
		
		
		public function getMethodRates($code,$title,$name,$rates)
		{
			$method = Mage::getModel('shipping/rate_result_method');  
            $method->setCarrier($this->_code);  
            $method->setCarrierTitle($title);
            $method->setMethod($this->_code.$code);  
            $method->setMethodTitle($name);
		    $method->setPrice($rates);
			
			return $method;
			
		}
		  

		/**
		 * Get allowed shipping methods
		 *
		 * @return array
		 */
		public function getAllowedMethods()
		{
			return array($this->_code=>$this->getConfigData('name'));
		}
    }  
