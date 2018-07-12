<?php 
function privatechannel()
	{
		global $footer;
		global $config;
		global $tsAdmin;
		global $user;
		global $language;
		$haschannel = false;
		$number1 = 0;
		$number2 = 0;
		$free1 = 0;
		$free2 = 0;

		$hasrang = false;
		$clientinchannel = array_keys(array_column($user['data'], 'cid'), $config['function']['privatechannel']['clientonchannel']);
		if(isset($clientinchannel[0]))
		{
			$clientinchannel = $clientinchannel[0];
			$servergroupclient = explode(',', $user['data'][$clientinchannel]['client_servergroups']);
			$clientdbid = $user['data'][$clientinchannel]['client_database_id'];
			$clientid = $user['data'][$clientinchannel]['clid'];
			$nick = $user['data'][$clientinchannel]['client_nickname'];
			foreach($servergroupclient as $group)
			{
				if(in_array($group, $config['function']['privatechannel']['needgroup']))
				{
					$hasrang = true;
					break;
				}
				else
				{
					$hasrang = false;
				}
			}
			if($hasrang==false)
			{
				$tsAdmin->sendMessage(1, $clientid, $language['privatechannel']['register']);
				return;
			}
			if($hasrang)
			{
				$userchannelgroup = $tsAdmin->channelGroupClientList(NULL, $clientdbid);
				if(empty($userchannelgroup['data']))
				{
					$haschannel = false;
				}
				else
				{ 

					for($i=0; $i<count($userchannelgroup['data']); $i++)
					{
						if($config['function']['privatechannel']['admingroup'] == $userchannelgroup['data'][$i]['cgid'])
						{
						$tsAdmin->clientPoke($clientid, "[b]".$language['privatechannel']['haschannel']."[/b]");
						$tsAdmin->clientMove($clientid, $userchannelgroup['data'][$i]['cid']);
						$haschannel = true;
						break;
						}
					}
				}
			}
			if(!$haschannel)
			{
				$channels = $tsAdmin->channelList('-topic');
				foreach($channels['data'] as $channel)
				{
					if($channel['pid'] == $config['function']['privatechannel']['channelzone'])	
					{	
						$free1++;
						$number1++;
						if($channel['channel_topic'] == $config['function']['privatechannel']['channeltopic'])
						{
							$channelname = str_replace('[NICK]', $nick, $config['function']['privatechannel']['channelname']);
							$date = date("d.m.o");
							$tsAdmin->clientMove($clientid, $channel['cid']);
							$tsAdmin->channelGroupAddClient($config['function']['privatechannel']['admingroup'], $channel['cid'], $clientdbid);
							$tsAdmin->channelEdit($channel['cid'], array
							(
							'channel_topic' => $date,
							'channel_name' => $number1.". ".$channelname,
							'channel_description' => "[center][size=15][b][img]https://www.iconfinder.com/icons/2123927/download/png/20[/img] ".$nick."[/b][/size]\n[size=12][color=blue][img]https://www.iconfinder.com/icons/2124097/download/png/20[/img] [b]".$language['privatechannel']['datecreated'].": ".$date."[/b][/color][/size][/center]".$footer,
							'channel_flag_maxclients_unlimited'=>1, 
							'channel_flag_maxfamilyclients_unlimited'=>1, 
							'channel_flag_maxfamilyclients_inherited'=>0,
							));
							$tsAdmin->clientPoke($clientid, "[b]".$language['privatechannel']['getchannel']." ".$number1."[/b]");
							$tsAdmin->clientPoke($clientid, "[b]".$config['function']['privatechannel']['messageafter']."[/b]");  
							for($e=0; $e<$config['function']['privatechannel']['subchannels']; $e++)
							{
							$number2++;
							$tsAdmin->channelCreate(array
								(
									'cpid' => $channel['cid'],
									'channel_name' => $number2." ".$config['function']['privatechannel']['subchannelname'], 
									'channel_flag_permanent' => 1, 
									'channel_flag_maxclients_unlimited' => 1, 
									'channel_flag_maxfamilyclients_inherited' => 1,
									'channel_flag_maxfamilyclients_unlimited' => 1
								));	
							}
							break;
						}
						else
						{	
							$free2++;
						} 
					}
				}
				if($free1==$free2)
				{
					$tsAdmin->clientPoke($clientid, "[b]".$language['privatechannel']['nofreechannels']."[/b]");
					$tsAdmin->clientKick($clientid, "channel");
				}
			}
		}
	}
?>