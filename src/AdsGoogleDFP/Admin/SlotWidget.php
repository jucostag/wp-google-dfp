<?php
namespace AdsGoogleDFP\Admin;

use AdsGoogleDFP\TwigViewer;

class SlotWidget extends \WP_Widget
{
	function __construct()
	{
		parent::__construct(
			'gdfp_banner', 
			'Google DFP Banner',
			[
				'description' => 'Widget para inserir espaços do Google DFP.',
			]
		);
	}

	public function widget($args, $options)
	{
		$options = $this->formatOptionNames($options);
		$content = array_merge($args, $options);
		
        echo TwigViewer::render("gdfp_widget.html", $content);
	}

	/**
	 * Padroniza os nomes das opções do form do Widget
	 * iguais as opções do shortcode.
	 * O form precisa manter os mesmos nomes nas opções
	 * para compatibilidade com dados já cadastrados.
	 *
	 * @param array $options
	 * @return $options 	No formato padronizado.
	 */
	private function formatOptionNames($options)
	{		
		return array_reduce(array_map(function($option, $value){
			$option = str_replace('gdfp_', '', $option);

			if ($option == 'title') {
				$value = apply_filters('widget_title', $value);
			}
			if ($option == 'area') {
				$value = preg_replace('/\s+|^\/|\/$/', '', strtolower($value));
			}

			return [$option => $value];

		}, array_keys($options), $options), 'array_merge', []);
	}
		
	/**
	 * Cria o form de cadastro do widget, mantendo os mesmos nomes
	 * nos campos para compatibilidade com dados já cadastrados
	 * através do plugin antigo.
	 *	
	 * @param array $instance 	A instancia criada.
	 */
	public function form($options)
	{
		$options = array_reduce(array_map(function ($optionName, $optionValue) {
			$optionKey = str_replace('gdfp_', '', $optionName);

			return [
				$optionKey => [
					'id' => $this->get_field_id($optionName),
					'name' => $this->get_field_name($optionName),
					'value' => esc_attr($optionValue),
				]
			];
		}, array_keys($options), $options), 'array_merge', []);
		
		echo TwigViewer::render("admin/gdfp_widget_form.html", $options);
	}
			
	/**
	 * Atualiza o widget, substituindo instâncias antigas 
	 * pelas informações inseridas.
	 * 
	 * @param  array $newInstance 	Novas informações.
	 * @param  array $oldInstance 	Informações anteriores.
	 * @return array  				Informações atualizadas.
	 */
	public function update($new, $old) {
		return [
			'title' => $this->formatValue($new['title']),
			'gdfp_area' => $this->formatValue($new['gdfp_area']),
			'gdfp_format' => $this->formatValue($new['gdfp_format']),
			'gdfp_before' => $this->formatValue($new['gdfp_before']),
			'gdfp_ad_class' => $this->formatValue($new['gdfp_ad_class']),
			'gdfp_title_class' => $this->formatValue($new['gdfp_title_class']),
		];
	}

	private function formatValue($value)
	{
		if (!empty($value)) {
			$value = strip_tags($value);
		}

		return $value;
	}

}