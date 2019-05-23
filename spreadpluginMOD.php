<?php
/**
 * Plugin Name: WP-SpreadpluginMOD
 * Description: Modifies the layout of the WP-Spreadplugin (https://wordpress.org/plugins/wp-spreadplugin/) by Thimo Grauerholz
 * Version:     1.0
 * Author:      Jeffrey Wong
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 */
require_once dirname(__DIR__)."/wp-spreadplugin/spreadplugin.php";
if(class_exists('WP_Spreadplugin')) {
    class WP_SpreadpluginMOD extends WP_Spreadplugin
    {
        /**
         * Class constructor
         */
        public function __construct()
        {
            parent::__construct();
            add_shortcode('spreadbasket', array(
                $this,
                'shoppingCartLink',
            ));
        }
        
        public static function inToCM($val)
        {
            return number_format($val / 0.393701, 2);
        }
        
        public static function shoppingCartLink()
        {
            $output = '<div style="background-color: #EEEEEE; margin-left: 1em; padding-left: 1em;">';
            $output .= '<div id="spreadplugin-menu" class="spreadplugin-menu">';
            $output .= '<div id="checkout" class="spreadplugin-checkout" style="font-size: 1.3em;"><a href="' . (!empty($_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']]) ? $_SESSION['checkoutUrl'][self::$shopOptions['shop_source'] . self::$shopOptions['shop_language']] : '') . '" target="' . self::$shopOptions['shop_linktarget'] . '" id="basketLink" class="spreadplugin-checkout-link' . (1 == self::$shopOptions['shop_basket_text_icon'] ? ' button' : '') . '">' . (0 == self::$shopOptions['shop_basket_text_icon'] ? __('Basket', 'spreadplugin') : '') . '<span></span></a></div>';
            $output .= '<div id="spreadplugin-cart" class="spreadplugin-cart"></div>';
            $output .= '</div></div>';
            return $output;
        }
        
        
        
        /**
         * override
         * No settings link to plugin required.
         */
        public function addPluginSettingsLink($links, $file)
        {
            
           /** static $this_plugin;
            if (!$this_plugin) {
                $this_plugin = plugin_basename(__FILE__);
            }

            if ($file == $this_plugin) {
                $settings_link = '<a href="options-general.php?page=splgMOD_options">' . __('Settings', 'spreadplugin') . '</a>';
                array_unshift($links, $settings_link);
            }*/

            return $links;
            
        }
        
        /**
         * Admin.
         */
        public function addPluginPage()
        {
            /*
            // Create menu tab
            add_options_page('Set SpreadpluginMOD options', 'SpreadpluginMOD', 'manage_options', 'splg_optionsMOD', array(
                $this,
                'pageOptions',
            ));
            */
        }
        
        /**
         * Function loadHead.
         * override to load nothing as original plugin is still working
         */
        public function loadHead()
        {
        }
        
        /**
         * Function enqueuesomes
         * override to load nothing as original plugin is still working
         */
         public function enqueueSomes()
         {
         }
         
         protected function prettyProductUrl($id)
        {
            //error_log($id);
            return get_site_url()."/shop/".self::$shopOptions['shop_url_productdetail_slug']."/".$id;
        }

         
        /**
         * Function displayArticles.
         *
         * Displays the articles
         *
         * @return html
         */
        protected function displayDetailPage($id, $article, $backgroundColor = '')
        {
            $useInches = true;
            if (!('en_US' == self::$shopOptions['shop_language'] || 'en_GB' == self::$shopOptions['shop_language'] || 'us_US' == self::$shopOptions['shop_language'] || 'us_CA' == self::$shopOptions['shop_language'] || 'fr_CA' == self::$shopOptions['shop_language'])) {
                $useInches = false;
            }

            $sku = $article['id'].'_'.$article['type'].'_'.$article['appearance'];

            /**
             * Google Microdata
             **
             * Disabled Elements:
             * "logo": "https://mosaic01.ztat.net/nvg/media/brandxl/7ee03a8e-534f-4a83-a574-8d3fafc5da34.jpg",
             * "manufacturer": "Nike Performance",
             * "color": "neonpink",
             * "aggregateRating": {
             *   "@type": "AggregateRating",
             *   "bestRating": 5,
             *   "ratingCount": 39,
             *   "ratingValue": "4.4615383",
             *   "reviewCount": 39,
             *   "worstRating": 1
             * },.
             */
            $output = '<script type="application/ld+json">
            {
              "@context": "http://schema.org",
              "@type": "Product",
              "image": "https://image.spreadshirt.'.self::$shopOptions['shop_source'].'/image-server/v1/products/'.urlencode($article['productid']).'/views/'.$article['view'].',width=600,height=600",
              "itemCondition": "http://schema.org/NewCondition",
              "name": "'.(!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '').(!empty($article['productname']) ? htmlspecialchars(' - '.$article['productname'], ENT_QUOTES) : '').'",
              "description": "'.htmlspecialchars(trim((!empty($article['description']) ? $article['description'] : '').' '.$article['productshortdescription']), ENT_QUOTES).'",
              "brand": "Spreadshirt",
              "sku": "'.$sku.'",
              "offers": [';

            $offerJson = '';
            if (!empty($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $offerJson .= '
                        {
                          "@type": "Offer",
                          "availability": "http://schema.org/InStock",
                          "price": "'.$article['pricebrut'].'",
                          "priceCurrency": "'.$article['currencycode'].'",
                          "itemOffered": {
                            "@context": "http://schema.org",
                            "@type": "Product",
                            "sku": "'.$sku.'-'.str_pad($k, 4, 0, STR_PAD_LEFT).'"
                          }
                        },';
                }

                $output .= trim($offerJson, ', ');
            }

            $output .= '
              ]

            }
            </script>';

            $output .= '<div class="spreadplugin-article-detail spreadplugin-item" id="article_'.$id.'">';
            $output .= '<a name="'.$id.'"></a>';
            
            // MOVED PRODUCT NAME HERE
            $output .= '<h1>'.(!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '').(!empty($article['productname']) ? '<span class="product-name-addon"> - '.htmlspecialchars($article['productname'], ENT_QUOTES).'</span>' : '').'</h1>';
            $output .= '<form method="post" id="form_'.$id.'">
            <div style="display: flex;">
            <div class="articleContentLeft">';

            // edit article button
            if (1 == self::$shopOptions['shop_designer']) {
                $output .= ' <div class="edit-wrapper-integrated" data-designid="'.$article['designid'].'" data-productid="'.(!empty($article['productid']) ? $article['productid'] : '').'" data-viewid="'.$article['view'].'" data-appearanceid="'.$article['appearance'].'" data-producttypeid="'.$article['type'].'"><i></i></div>';
            }

            $imgSrc = '//image.spreadshirt.'.self::$shopOptions['shop_source'].'/image-server/v1/products/'.urlencode($article['productid']).'/views/'.$article['view'].',width=800,height=800,appearanceId='.$article['appearance'].',typeId='.$article['type'];

            if (!empty(self::$shopOptions['shop_modelids'])) {
                $modelId = self::returnModelId($article, self::$shopOptions);
                $imgSrc .= ',modelId='.$modelId.',crop=list,version='.time();
            } elseif (!empty($article['viewModelId'])) {
                $imgSrc .= ',modelId='.$article['viewModelId'].',crop=list,version='.time();
            }

            // display preview image
            $output .= '<div class="image-wrapper">';
            $output .= '<img src="'.$imgSrc.'" class="preview" style="height:280px"  alt="'.(!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '').(!empty($article['productname']) ? htmlspecialchars(' - '.$article['productname'], ENT_QUOTES) : '').'" id="previewimg_'.$id.'" data-zoom-image="'.$imgSrc.(!empty($backgroundColor) ? ',backgroundColor='.$backgroundColor : '').'" />';
            $output .= '</div>';


            // add a list with available product views
            if (isset($article['views']) && is_array($article['views'])) {
                $output .= '<div class="views-wrapper"><ul class="views" name="views">';

                foreach ($article['views'] as $k => $v) {
                    $output .= '<li value="'.$k.'"><img src="//image.spreadshirt.'.self::$shopOptions['shop_source'].'/image-server/v1/products/'.urlencode($article['productid']).'/views/'.$article['view'];
                    $output .= ',width=100,height=100,appearanceId='.$article['appearance'].',typeId='.$article['type'].',viewId='.$k.'" class="previewview" alt="" id="viewimg_'.$id.'" /></li>';
                }

                $output .= '</ul></div>';
                
                
                //////////// MOVED COLOR SELECTION /////////////
                // add a list with availabel product colors
                if (isset($article['appearances']) && is_array($article['appearances'])) {
                    $output .= '<div class="color-wrapper spreadplugin-clearfix"><span>'.__('Color', 'spreadplugin').':</span> <ul class="colors" name="color">';
    
                    foreach ($article['appearances'] as $k => $v) {
                        $output .= '<li value="'.$k.'"><div class="spreadplugin-color-item" style="background-color:'.(!empty($v['color']) ? strtoupper($v['color']) : '').'" title="'.(is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? __('Out of stock', 'spreadplugin') : '').'" class="'.(is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? 'spreadplugin-not-on-stock' : '').'"></div></li>';
                    }
    
                    $output .= '</ul></div>';
                }
                ///////////// END MOVED COLOR SELECTION//////////
            }
            
            //////// ORIGINAL SHORT PRODUCT DESCRIPTION ///////////
            /**
            // Short product description
            $output .= '<div class="product-name">';
            $output .= htmlspecialchars($article['productname'], ENT_QUOTES);
            $output .= '</div>';
            
            // if (1 == self::$shopOptions['shop_enablelink']) {
            //     $output .= ' <div class="details-wrapper2"><a href="//'.self::$shopOptions['shop_id'].'.spreadshirt.'.self::$shopOptions['shop_source'].'/-A'.$id.'" target="_blank">'.__('Additional details', 'spreadplugin').'</a></div>';
            // }
            */
            /////// END ORIGINAL SHORT PRODUCT DESCRIPTION //////////
            
            
            /////////// ORIGINAL PRODUCT NAME /////////////////////
            //$output .= '</div><div class="articleContentRight"><h1>'.(!empty($article['name']) ? htmlspecialchars($article['name'], ENT_QUOTES) : '').(!empty($article['productname']) ? '<span class="product-name-addon"> - '.htmlspecialchars($article['productname'], ENT_QUOTES).'</span>' : '').'</h1>';
            ///////////////////////////////////////////////////////
            /////////// MODIFIED///////////////////////////////////
            $output .= '</div><div class="articleContentRight">';
            /////////// END ///////////////////////////////////////
            
            
            // Show description link if not empty
            if (!empty($article['description'])) {
                //$output .= '<div class="description-wrapper spreadplugin-clearfix">'.htmlspecialchars($article['description'], ENT_QUOTES).'</div>';
                $output .= '<div class="description-wrapper spreadplugin-clearfix" style="flex-grow: 3;"><b>About Design</b><br />'.htmlspecialchars($article['description'], ENT_QUOTES).'</div>';
            }

            //////////// ORIGINAL PRODUCT DESC ////////////////
            /**
            // Show product description
            $output .= '<div class="product-description-wrapper spreadplugin-clearfix"><strong>'.__('Product details', 'spreadplugin').'</strong><div>'.$article['productshortdescription'].'</div></div>';

            $output .= '<div style="font-size: smaller; padding-bottom:10px">#<span>'.$sku.'</span></div>';
            */
            //////////// END ORIGINAL PRODUCT DESC ////////////
            
            //////////// ORIGINAL COLOR SELECTION /////////////
            /**
            // add a list with availabel product colors
            if (isset($article['appearances']) && is_array($article['appearances'])) {
                $output .= '<div class="color-wrapper spreadplugin-clearfix"><span>'.__('Color', 'spreadplugin').':</span> <ul class="colors" name="color">';

                foreach ($article['appearances'] as $k => $v) {
                    $output .= '<li value="'.$k.'"><div class="spreadplugin-color-item" style="background-color:'.(!empty($v['color']) ? strtoupper($v['color']) : '').'" title="'.(is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? __('Out of stock', 'spreadplugin') : '').'" class="'.(is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? 'spreadplugin-not-on-stock' : '').'"></div></li>';
                }

                $output .= '</ul></div>';
            }
            */
            ///////////// END ORIGINAL COLOR SELECTION//////////
            
            ///////////// ORIGINAL SIZE SELECTION //////////////
            /*
            // add a select with available sizes
            if (isset($article['sizes']) && is_array($article['sizes'])) {
                $output .= '<div class="size-wrapper"><span>'.__('Size', 'spreadplugin').':</span> <div class="style-select-size"><select class="size-select" name="size">';

                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<option value="'.$k.'"'.(is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? ' disabled="disabled" title="'.__('Out of stock', 'spreadplugin').'"' : '').'>'.(!empty($v['name']) ? $v['name'] : $k).'</option>';
                }

                $output .= '</select></div></div>';
            }
            */
            ///////////// END ORIGINAL SIZE SELECTION //////////////
            
        
            ///////////// MODIFIED SIZE SELECTION //////////////
            // add a select with available sizes
            if (isset($article['sizes']) && is_array($article['sizes'])) {
                $output .= '<div class="size-wrapper"><span>'.__('Size', 'spreadplugin').':</span><div style="display: flex; flex-wrap:wrap; justify-content: flex-start;">';

                foreach ($article['sizes'] as $k => $v) {
                    //$output .= '<option value="'.$k.'"'.(is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? ' disabled="disabled" title="'.__('Out of stock', 'spreadplugin').'"' : '').'>'.(!empty($v['name']) ? $v['name'] : $k).'</option>';
                    $output .= '<label class="labl">
                        <input type="radio" name="size" value="'.$k.'"'.(is_array($v) && array_key_exists('onStock', $v) && 0 == $v['onStock'] && 1 == self::$shopOptions['shop_stockstates'] ? ' disabled="disabled" class="sizeOut" title="'.__('Out of stock', 'spreadplugin').'"' : '').'/>
                        <div>'.(!empty($v['name']) ? $v['name'] : $k).'</div>
                        </label>';
                    
                }

                $output .= '</div></div>';
            }
            ///////////// END MODIFIED SIZE SELECTION //////////////
            
            ///////////// ORIGINAL QUANTITY ////////////////////////
            //$output .= '<div class="quantity-wrapper"><span>'.__('Quantity:', 'spreadplugin').'</span> <input type="text" value="1" class="quantity" name="quantity" maxlength="4" /></div>';
            ///////////// MODIFIED QUANTITY /////////////////////////
            $output .= '<div class="quantity-wrapper"><span>'.__('Quantity:', 'spreadplugin').'</span> <input style="display: inline;" type="text" value="1" class="quantity" name="quantity" maxlength="4" /></div>';
            ///////////// END ORIGINAL QUANTITY//////////////////////
            
            
            $output .= '<input type="hidden" value="'.$article['appearance'].'" id="appearance" name="appearance" />';
            $output .= '<input type="hidden" value="'.$article['view'].'" id="view" name="view" />';
            $output .= '<input type="hidden" value="'.$article['sellableId'].'" id="sellableId" name="sellableId" />';
            $output .= '<input type="hidden" value="'.$article['ideaId'].'" id="ideaId" name="ideaId" />';
            $output .= '<input type="hidden" value="'.$article['appearance'].'" id="defaultAppearance" name="defaultAppearance" />';
            $output .= '<input type="hidden" value="'.(!empty($article['sellableProductId']) ? $article['sellableProductId'] : $article['productid']).'" id="article" name="article" />';
            $output .= '<input type="hidden" value="1" id="type" name="type" />';

            // $output .= '<div class="separator"></div>';
            $output .= '<div class="price-wrapper spreadplugin-clearfix">';
            if (1 == self::$shopOptions['shop_showextendprice']) {
                //$output .= '<span id="price-without-tax">'.__('Price (without tax):', 'spreadplugin').' '.self::formatPrice($article['pricenet'], $article['currencycode']).'<br /></span>';
                $output .= '<span id="price-with-tax">'.__('Price (with tax):', 'spreadplugin').' '.self::formatPrice($article['pricebrut'], $article['currencycode']).'</span>';
                $output .= '<br><div class="additionalshippingcosts">';
                $output .= __('excl. <a class="shipping-window">Shipping</a>', 'spreadplugin');
                $output .= '</div>';
            } else {
                $output .= '<div class="price-container"><div class="price-slug">'.__('Price:', 'spreadplugin').'</div> <div class="price">'.self::formatPrice($article['pricebrut'], $article['currencycode']).'</div></div>';
            }
            $output .= '</div>';

            // order buttons
            $output .= '<input type="submit" name="submit" value="'.__('Add to basket', 'spreadplugin').'" />';

            $output .= '<div class="addtocart-claims">';
            if (!empty(self::$shopOptions['shop_claimcheck1'])) {
                $output .= '<div class="claims-row"><span class="claims-check">✓</span> '.self::$shopOptions['shop_claimcheck1'].'</div>';
            }
            if (!empty(self::$shopOptions['shop_claimcheck2'])) {
                $output .= '<div class="claims-row"><span class="claims-check">✓</span> '.self::$shopOptions['shop_claimcheck2'].'</div>';
            }
            $output .= '</div>';

            $output .= '<br>';

            // Social buttons
            if (true == self::$shopOptions['shop_social']) {
                $output .= '
				<ul class="soc-icons">
				<li><a target="_blank" data-color="#5481de" class="fb" href="//www.facebook.com/sharer.php?u='.urlencode($this->prettyProductUrl($id)).'&t='.rawurlencode(get_the_title()).'" title="Facebook"><i></i></a></li>
				<li><a target="_blank" data-color="#06ad18" class="goog" href="//plus.google.com/share?url='.urlencode($this->prettyProductUrl($id)).'" title="Google"><i></i></a></li>
				<li><a target="_blank" data-color="#2cbbea" class="twt" href="//twitter.com/home?status='.rawurlencode(get_the_title()).' - '.urlencode($this->prettyProductUrl($id)).'" title="Twitter"><i></i></a></li>
				';
                $output .= '<li><a target="_blank" data-color="#e84f61" class="pin" href="//pinterest.com/pin/create/button/?url='.rawurlencode($this->prettyProductUrl($id)).'&media='.rawurlencode('https://image.spreadshirt.'.self::$shopOptions['shop_source'].'/image-server/v1/products/'.$article['productid'].'/views/'.$article['view'].',width=280,height=280').',width='.self::$shopOptions['shop_imagesize'].',';
                $output .= 'height='.self::$shopOptions['shop_imagesize'].'&description='.(!empty($article['description']) ? htmlspecialchars($article['description'], ENT_QUOTES) : 'Product').'" title="Pinterest"><i></i></a></li>
				</ul>
				';
            }
            $output .= '
			</div>
			</div> <!-- container flex -->
			</form>
			';

            $output .= '
<div id="spreadplugin-tabs_wrapper">
	<div id="spreadplugin-tabs_content_container">
  <h2 class="spreadplugin-details-headline">'.__('Product Details', 'spreadplugin').'</h2>
  ';
  
  /** GALLERY INSERTED HERE */
  //$output .= do_shortcode( '[maxgallery name="test"]' );
  $output .= do_shortcode( '[maxgallery name="'.$article['id'].'"]' );
  //echo $id;
  /** GALLERY INSERTED HERE */
  
  /////////////// ORIGINAL PRODUCT DETAILS AND SIZE ////////////////////////
  /** $output .= '
  <div id="tab3" class="spreadplugin-tab_content">
    <p>'.$article['productdescription'].'</p>
  </div>
		<div id="tab2" class="spreadplugin-tab_content">
			<img alt="" src="https://image.spreadshirtmedia.net/image-server/v1/productTypes/'.$article['type'].'/variants/size,width=130,height=130">

			<table class="assort_sizes">
			<thead>
			<tr>
			<th>'.__('Size', 'spreadplugin').'</th>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<th>'.(!empty($v['name']) ? $v['name'] : $k).'</th>';
                }
            }

            $output .= '
			</tr>
			</thead>
			<tbody>
			<tr>
			<td>'.__('Dimension', 'spreadplugin').' A ('.($_toInches ? 'inch' : 'cm').')</td>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<td>'.(!empty($v['measures'][0]['value']) ? ($_toInches ? self::inToCM($v['measures'][0]['value']) : $v['measures'][0]['value']) : $k).'</td>';
                }
            }

            $output .= '
			</tr>
			<tr class="even">
			<td>'.__('Dimension', 'spreadplugin').' B ('.($_toInches ? 'inch' : 'cm').')</td>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<td>'.(!empty($v['measures'][1]['value']) ? ($_toInches ? self::inToCM($v['measures'][1]['value']) : $v['measures'][1]['value']) : $k).'</td>';
                }
            }

            $output .= '
			</tr>
			</tbody>
			</table>
			';
	*/
    ///////////////////// END ORIGINAL PRODUCT DETAILS AND SIZE /////////////////////
    
    /////////////// MODIFIED PRODUCT DETAILS AND SIZE ////////////////////////
  $output .= '
  <div class="spreadplugin-tab_container">
    <p>'.$article['productdescription'].'</p>
  </div>
  
  
  <h2 class="spreadplugin-details-headline">'.__('Size Information', 'spreadplugin').'</h2>
		<div class="spreadplugin-tab_container">
		Product dimensions measured on a flatly laid out product. Measure an item of clothing at hand to compare.<br /><br />
			<div style="display: flex">
			<img alt="" height="100" src="https://image.spreadshirtmedia.net/image-server/v1/productTypes/'.$article['type'].'/variants/size,width=130,height=130">
            <img alt="" height="100" src="https://image.spreadshirtmedia.net/image-server/v1/productTypes/'.$article['type'].'/variants/detail,width=560,height=150">
			</div>
			<table class="assort_sizes">
			  <colgroup>
                <col style="width:20%">
              </colgroup> 
			<thead>
			<tr>
			<th>'.__('Size', 'spreadplugin').'</th>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<th>'.(!empty($v['name']) ? $v['name'] : $k).'</th>';
                }
            }

            $output .= '
			</tr>
			</thead>
			<tbody>
			<tr>
			<td>'.__('Dimension', 'spreadplugin').' A ('.($useInches ? 'inch' : 'cm').')</td>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<td>'.(!empty($v['measures'][0]['value']) ? ($useInches ? $v['measures'][0]['value'] : self::inToCM($v['measures'][0]['value'])) : $k).'</td>';
                }
            }

            $output .= '
			</tr>
			<tr class="even">
			<td>'.__('Dimension', 'spreadplugin').' B ('.($useInches ? 'inch' : 'cm').')</td>
			';

            if (isset($article['sizes']) && is_array($article['sizes'])) {
                foreach ($article['sizes'] as $k => $v) {
                    $output .= '<td>'.(!empty($v['measures'][1]['value']) ? ($useInches ? $v['measures'][1]['value'] : self::inToCM($v['measures'][1]['value'])) : $k).'</td>';
                }
            }
            $output .= '
			</tr>';
			
			// MOD: Sometimes there's a dimension C
            // No C dimension in article ???
            if(isset($article['sizes']) && is_array($article['sizes']))
            {
                reset($article['sizes']);
                /*$someRandomKey = key($article['sizes']);
                if(isset($article['sizes'][$someRandomKey][2]))
                {*/
                  $output .= '
    			<tr class="even">
    			<td>'.__('Dimension', 'spreadplugin').' C ('.($useInches ? 'inch' : 'cm').')</td>
    			';
    			    foreach ($article['sizes'] as $k => $v) {
                        $output .= '<td>'.(!empty($v['measures'][2]['value']) ? ($useInches ? $v['measures'][2]['value'] : self::inToCM($v['measures'][2]['value'])) : $k).'</td>';
                    }
                $output .='</tr>';
                //}
            }
            
            $output .='
			</tbody>
			</table>
			';
		
		    // MY DEBUG: print article array
		  /*  
			echo "<pre>";
            print_r($article);
            echo "</pre>";
      */
            
            
    ///////////////////// END MODIFIED PRODUCT DETAILS AND SIZE /////////////////////
    
    ///////////////////// ORIGINAL SHIRT DIMENSIONS ////////////////////////////////
           /* $output .= '
		</div>
    <div id="tab1" class="spreadplugin-tab_content">
    			<p><img alt="" src="https://image.spreadshirtmedia.net/image-server/v1/productTypes/'.$article['type'].'/variants/detail,width=560,height=150"></p>
    		</div>
	';*/
		///////////////////// END ORIGINAL SHIRT DIMENSIONS ////////////////////////////
	

            if (!empty($article['printtypename'])) {
                $output .= '
			<div id="tab4" class="spreadplugin-tab_content">
				<p><strong>'.$article['printtypename'].'</strong></p>
				<p>'.$article['printtypedescription'].'</p>
			</div>
			';
            }
            $output .= '</div>
</div>
			';
			
			///////////////// TODO: ADD MORE PRODUCTS MOD /////////////////////////////////////
			//$output .= do_shortcode( '[spreadplugin shop_productcategory="'.$article['designid'].'"]');
			/////////////////////////////////////////////////////////////////////////////
			
      $output .= '</div>';

            return $output;
        }
        
    }
    
    

    new WP_SpreadpluginMOD();
}


