<?php
session_start();
error_reporting(0);
class kontrol{
	
	protected $yasakKarakter = array(
			"/","\"","'","&","\\","%","$","#","@","€","[","]","{","}","*","?","=","^","<",">","!","~",",",";","|","´","`"
		);
	protected $hata = array(
		2 => 'Belirttiğiniz alan adında kullanılmayan karakterler var',
		3 => 'Baglanti kurulamiyor',
		4 => 'Herhangi bir uzantı seçmediniz veya istenilen uzantı sorgu listemizde mevcut değil',
		5 => 'Alan adınızı boş bıraktınız veya farklı bir problem var',
	);

	
	protected $servers = array(
		"biz" => "whois.neulevel.biz",
		"com" => "whois.internic.net",
		"us" => "whois.nic.us",
		"coop" => "whois.nic.coop",
		"info" => "whois.nic.info",
		"name" => "whois.nic.name",
		"net" => "whois.internic.net",
		"gov" => "whois.nic.gov",
		"edu" => "whois.internic.net",
		"mil" => "rs.internic.net",
		"int" => "whois.iana.org",
		"ac" => "whois.nic.ac",
		"ae" => "whois.uaenic.ae",
		"at" => "whois.ripe.net",
		"au" => "whois.aunic.net",
		"be" => "whois.dns.be",
		"bg" => "whois.ripe.net",
		"br" => "whois.registro.br",
		"bz" => "whois.belizenic.bz",
		"ca" => "whois.cira.ca",
		"cc" => "whois.nic.cc",
		"ch" => "whois.nic.ch",
		"cl" => "whois.nic.cl",
		"cn" => "whois.cnnic.net.cn",
		"cz" => "whois.nic.cz",
		"de" => "whois.nic.de",
		"fr" => "whois.nic.fr",
		"hu" => "whois.nic.hu",
		"ie" => "whois.domainregistry.ie",
		"il" => "whois.isoc.org.il",
		"in" => "whois.ncst.ernet.in",
		"ir" => "whois.nic.ir",
		"mc" => "whois.ripe.net",
		"to" => "whois.tonic.to",
		"tv" => "whois.nic.tv",
		"ru" => "whois.ripn.net",
		"org" => "whois.pir.org",
		"aero" => "whois.information.aero",
		"nl" => "whois.domain-registry.nl",
		"com.tr" => "whois.nic.tr",
		"gen.tr" => "whois.nic.tr",
		"web.tr" => "whois.nic.tr",
		"k12.tr" => "whois.nic.tr",
		"org.tr" => "whois.nic.tr"
	);
	
	protected $domain = 'evsanati.com';
	protected $domainBilgi = array();
	
	protected function domainSorgula($domain,$uzanti){
		
		$karakter = strlen($domain);
		for($i = 0; $i < $karakter; $i++){
			if(@in_array($domain[$i], $this->yasakKarakter)){
				$this->domainBilgi[$uzanti]['hata'] = 2;
				return false;
			}
		}

		if(empty($this->servers[$uzanti])){
			$this->domainBilgi[$uzanti]['hata'] = 4;
			return false;
		}
		
		$baglan = $this->servers[$uzanti];
		
		$output = '';
		try{
			if ($conn = fsockopen ($baglan, 43)) {
				fputs($conn, $domain.'.'.$uzanti."\r\n");
				while(!feof($conn)) {
					$output .= fgets($conn,128);
				}
				fclose($conn);
			}
			else {
				$this->domainBilgi[$uzanti]['hata'] = 3;
			}
			
		}catch(exception $e){
			$this->domainBilgi[$uzanti]['hata'] = 3;
		}
		
		$this->domainBilgi[$uzanti]['whois'] = $output;
		if(stristr($output,"No match") || stristr($output,"No entries" ) || stristr($output, "NOT FOUND" ) ){
			$this->domainBilgi[$uzanti]['durum'] = 0;
		}
		else {
			$this->domainBilgi[$uzanti]['durum'] = 1;
		}

	}


	protected function domainkontrol(){
		
		$domain = strip_tags($_POST['alanadi']);
		$this->domain = $this->domainPakle($domain);
		
		if(@is_array($_POST['uzanti'])){
			$uzantilar = array_map('strip_tags',$_POST['uzanti']);
		}else{
			echo '<div class="notice">Hiçbir uzantı seçmediniz.</div>';
			return false;
		}
		
		foreach($uzantilar as $uzanti){
			$uzanti = strtolower(trim($uzanti));
			$this->domainSorgula($this->domain, $uzanti);	
		}
		
		echo '
		<h3 class="title">'.$this->domain.' Bilgileri</h3>
		<table border="0" width="100%" cellSpacing="0" cellPadding="0" class="domainler">';
		foreach ($this->domainBilgi as $key=>$value) {
			if(!empty($value['hata'])){
				$text1 = $this->hata[$value['hata']];
				$text2 = '';
				$class= "hata";
			}elseif($value['durum']){
				$domainAd = '<a href="http://www.'.$this->domain.'.'.$key.'">'.$this->domain.'.'.$key.'</a>';
				$text1 = '<span class="kirmizi">Dolu</span>';
				$text2 = '<span class="link">Detay</span><div class="popDiv none"><div class="title">'.$this->domain.'.'.$key.' Bilgileri <span class="close">x</span></div><div class="whoScrol"><pre>'.$value['whois'].'</pre></div></div>';
				$class= "dolu";
				
			}else{
				$domainAd = $this->domain.'.'.$key;
				$text1 = '<span class="yesil">Müsait</span>';
				$text2 = '<a target="_blank" href="http://ornekhosting.com"><img src="./images/satinal.png" id="ters" alt="Satın Al" title="Satın Al"></a>';
				$class = "bos";
			
			}
			
			echo '
			<tr class="'.$class.'">
				<td>'.$domainAd.'</td>
				<td>'.$text1.'</td>
				<td>'.$text2.'</td>
			</tr>';
		}	
		echo '</table>';
		
	}

	protected function domainPakle($domain){
		$domain = strtolower(trim($domain));
		$domain = str_replace(array('www.','http://'), array('',''), $domain);
		$slac = explode('/', $domain);
		$nokta = explode('.',$slac[0]);
		return $nokta[0];
	}
	
	public function __construct(){
		if(!$_POST){return;}
		
		if($_POST['formVeriAL'] == $_SESSION['formVeriAL']){
			$this->domainkontrol();
		}else{
			echo '<div class="notice">Üzgünüm bir hata oluştu ve sorgulama yapılmadı (Eksik Parametre).</div>'; 
		}

	}
	
}


	$kontrol = new kontrol();

?>
