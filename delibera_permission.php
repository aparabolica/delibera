<?php
/**
 * Verifica se o usuário atual pode participar das discussão
 * de uma pauta votando ou discutindo.
 *
 * Por padrão retorna true apenas de o usuário tiver a capability 'votar',
 * mas se a opção "Todos os usuários da rede podem participar" estiver habilitada
 * retorna true para todos os usuários logados.
 *
 * Quando estiver na single da pauta, retorna false sempre que ela
 * estiver com o prazo encerrado.
 *
 * @param string $permissao
 * @return bool
 * @package Pauta
 */
function delibera_current_user_can_participate($permissao = 'votar') {
	global $post;

	$options = delibera_get_config();

	if (is_singular('pauta') && \Delibera\Flow::getDeadlineDays($post->ID) == -1) {
		return false;
	} else if (is_multisite() && $options['todos_usuarios_logados_podem_participar'] == 'S') {
		return is_user_logged_in();
	} else {
		return current_user_can($permissao);
	}
}

/**
 * Verifica se pauta está aberta para comentários
 *
 * @param string $postID
 *
 * @package Pauta
 */
function delibera_can_comment($postID = '')
{
	if(is_admin()) return true;

	if(is_null($postID))
	{
		$post = get_post($postID);
		$postID = $post->ID;
	}

	$situacoes_validas = array('validacao' => true, 'discussao' => true, 'emvotacao' => true, 'elegerelator' => true);
	$situacao = delibera_get_situacao($postID);

	if(array_key_exists($situacao->slug, $situacoes_validas))
	{
		return delibera_current_user_can_participate();
	}
	elseif($situacao->slug == 'relatoria')
	{
		return current_user_can('relatoria');
	}
	return false;
}