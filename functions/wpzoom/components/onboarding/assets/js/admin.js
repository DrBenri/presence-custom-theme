( function( $ ) {
	const $wrapper = $('.wpz-onboard_wrapper');
	const activePlugins = [];
	const requiredPlugins = [];
	const recommendedPlugins = [];

	/**
	 * Hook up functionality when the document loads.
	 */
	$( function( $ ) {
		if ( window.location.hash ) {
			setTab( window.location.hash );
		}

		$(window).on('wpzoom-onboarding-pointers-init', function() {
			if ( window.location.hash ) {
				setTimeout(() => {
					wpzoomOnboardingTour.updatePointersOnTabChange( window.location.hash );
				});
			}
		});

		$(document).on("click", "body", (event) => {
			// Hide dropdown actions when click outside the box.
			if ( !$(event.target).hasClass('button-toggle-dropdown-actions') && !$(event.target).closest('.wpz-onboard_snapshot-dropdown-actions.shown').length) {
				$wrapper.find('.wpz-onboard_snapshot-dropdown-actions.shown').removeClass('shown');
			}
		});

		$wrapper.find( '.wpz-onboard_content-main-step-toggle-link' ).on( 'click', function( event ) {
			event.preventDefault();

			const $step = $(this).closest('.wpz-onboard_content-main-step');
			const stepIndex = $wrapper.find('.wpz-onboard_content-main-step').index($step);
			$step.toggleClass('active');
			$(this).toggleClass('active');
			
			const isOpen = $step.hasClass('active');
			$(this).text(isOpen ? 'close' : 'open');
			
			document.cookie = `wpzoom_step_${stepIndex}=${ isOpen ? 'open' : 'closed' }; path=/; max-age=${30*24*60*60}`;
			
			$step.find('.wpz-onboard_filter-designs').slideToggle();
			$step.find('.wpz-onboard_content-main-step-content').slideToggle();
		} );

		$wrapper.find( '#wpz-onboard_tabs a, .wpz-onboard_notice a#license-tab-link, .quick-action-section a#license-tab-link' ).on( 'click', function() {
			const hash = $( this ).attr( 'href' );
			const id = $(this).attr('id');

			setTab( hash );
			
			if ( 'license-tab-link' === id ) {
				if ( $(`[data-id="${hash}"]`).length ) {
					$('html, body').animate({
						scrollTop: $(`[data-id="${hash}"]`).closest('.wpz-onboard_wrapper').offset().top
					}, 800);	
				}
			}
		} );
		$wrapper.find( '.wpz-onboard_snapshots-table a.button-toggle-dropdown-actions' ).on( 'click', function( event ) { 
			event.preventDefault();
			const toggleId = $(this).attr('data-toggle');
			$(document).find(`#${toggleId}`).toggleClass('shown');
		} );
		$wrapper.find('.wpz-onboard_notice li[data-notice="third-party-required"] .go-up-link').on('click', function( event ){
			event.preventDefault();
			gotoNextStep('#step-install-plugins');
		});
		$wrapper.find('.wpz-onboard_notice a#wpz-onboard-skip-notice').on('click', function(event){
			event.preventDefault();
			toggleNotice();
			$(this).remove();
		});

		initConstants();
		pluginInstallStep();
		themeDesignStep();
		demoContentStep();
		activateLicenseKey();
		demoPluginsSortable();
		pluginsButtonState();
		builderSelectionStep();

	} );

	function initConstants() {
		$wrapper.find('#step-install-plugins .plugins-list > li').each(function(){
			const slug = $(this).find('input[name="required_plugins[]"]').val();
			if ( $(this).hasClass('plugin-level_required') ) {
				requiredPlugins.push(slug);
			} else if ( $(this).hasClass('plugin-level_recommended') ) {
				recommendedPlugins.push(slug);
			}
			if ( $(this).hasClass('plugin-status_active') ) {
				activePlugins.push(slug);
			}
		})
	}

	/**
	 * Sets the selected tab to the tab with the given ID.
	 *
	 * @param {string} id The ID of the tab to set as selected.
	 */
	function setTab( id, updateHash = false ) {
		if ( id ) {
			const $target = $wrapper.find( '#wpz-onboard_tabs .wpz-onboard_tab a[href="' + id + '"]' ),
			      $tabs   = $target.closest( '.wpz-onboard_wrapper' ).find( '.wpz-onboard_content .wpz-onboard_content-main .wpz-onboard_content-main-tab' );

			$target.closest( '#wpz-onboard_tabs' ).find( '.wpz-onboard_tab' ).removeClass( 'active' );
			$target.closest( '.wpz-onboard_tab' ).addClass( 'active' );

			$tabs.removeClass( 'active' );
			$tabs.filter( '[data-id="' + id + '"]' ).addClass( 'active' );

			if ( window.hasOwnProperty('wpzoomOnboardingTour') ) {
				wpzoomOnboardingTour.updatePointersOnTabChange( id );
			}

			// Add hash (#) to URL when set new tab active.
			if ( updateHash ) {
				window.location.hash = id;
			}
		}
	}

	/**
	 * Handles the required plugin install step.
	 */
	function pluginInstallStep() {
		const $stepWrap          = $( '.step-install-plugins', $wrapper ),
		      $form              = $( '.wpz-onboard_content-main-step-content form', $stepWrap ),
		      $pluginsCheckboxes = $( '> fieldset > ul input[name="required_plugins[]"]', $form ),
		      $submitButton      = $( 'input[type="submit"]', $stepWrap ),
		      $checkButton       = $( 'input[type="button"][name="button_checkall"]', $stepWrap );

		$pluginsCheckboxes.on( 'change', onPluginInstallCheckboxChange );
		$submitButton.on( 'click', onPluginInstallSubmitClick );
		$checkButton.on( 'click', onPluginInstallCheckboxesToggleClick );

		updatePluginInstallButtons( $form );
	}

	/**
	 * Handles builder selection step.
	*/
	function builderSelectionStep() {
		const $stepWrap = $( '.step-choose-builder', $wrapper ),
			$form = $('form', $stepWrap),
			$designItems = $('.wpz-onboard_content-main-step-content form > fieldset > ul > li', $stepWrap);
			$builder = $stepWrap.find( 'input:radio[name=builder]' ); 
			if ( $builder.length ) {
				demoPluginsSortableByBuilder();
				$builder.on( 'change', function() {
					const $item = $(this);
					var val = $(this).val();
					var design_name = $(this).data('design-name');
					var theme_design = $(this).data('theme-design');
					$(this).append('<div class="wpz-onboard_dot-elastic"></div>');

					onThemeDesignChange( $item, theme_design, $form, $designItems );

					if( design_name )  {
						$wrapper.find( '.step-import-demo .wpz-onboard_selected-template > strong' ).text( design_name );	
					}

				} );
			};
	}

	/**
	 * Handles the theme design selection step.
	 */
	function themeDesignStep() {
		const $stepWrap = $( '.step-choose-design', $wrapper ),
			$form = $('form', $stepWrap),
			$designItems = $('.wpz-onboard_content-main-step-content form > fieldset > ul > li', $stepWrap);

		$designItems.each( function(){
			const $item = $(this);
			const $previewThumbnail = $item.find('figure .preview-thumbnail');
			const $viewPagesLink = $item.find('a.view-pages-open');
			const design = $item.find( 'figure input[name="theme_designs"]' ).val();
			
			$previewThumbnail.on('click', function(event) {
				event.preventDefault();
				
				const isSelected = $item.hasClass('selected-template');
				const isChecked = $item.find('input[name="theme_designs"]').is(':checked');

				if ( ! isSelected && ! isChecked ) {

					$item.addClass('preparing-view-template');
					$item.find('.button-select-template').text( wpzoomOnboarding.labels.processing_template_data );
					$(this).append('<div class="wpz-onboard_dot-elastic"></div>');

					onThemeDesignChange( $item, design, $form, $designItems );
				}
			})

			$viewPagesLink.on('click', function(event) {
				event.preventDefault();
				event.stopPropagation();

				const isPreparingPreview = $item.hasClass('preparing-view-template');

				if ( ! isPreparingPreview ) {
					$item.addClass('preparing-view-template');
					$item.find('.button-select-template').text( wpzoomOnboarding.labels.preparing_preview );
					$previewThumbnail.append('<div class="wpz-onboard_dot-elastic"></div>');

					onPreparingTemplatePreview( design, $form );
				}
			})
		})
	}

	/**
	 * Handles the demo content import step.
	 */
	function demoContentStep() {
		const $stepWrap     = $( '.step-import-demo', $wrapper ),
		      $importButton = $( '.wpz-onboard_import-button', $stepWrap );

		// $importButton.on( 'click', onDemoImportLoadClick );
	}


	/**
	 * Handles the demo required only plugins.
	 * 	
	 * @param {string} builder The builder to process data for.
	 * 	
	 */
	 
	function demoPluginsSortableByBuilder() {

		const $stepWrap   = $( '.step-choose-design', $wrapper ),
		$stepWrapBuilders = $( '.step-choose-builder', $wrapper ),
		$builder          = $stepWrapBuilders.find( 'input:radio[name=builder]' ),
		pluginsWrapper    = $( '.step-install-plugins', $wrapper );
		pluginsForm       = $( '.wpz-onboard_content-main-step-content form', pluginsWrapper );

		if ( $builder.length > 1 ) {

			$selectedBuilder = $builder.filter(':checked');
			$( '.wpz-onboard_import-button' ).addClass( 'disabled' );

			pluginsWrapper.find( '[data-plugin-sortable=true]' ).hide();
			pluginsWrapper.find( '[data-plugin-sortable=true] input').prop('disabled', true );
			
			var design_id = $selectedBuilder.data('design-id');
			pluginClassname = '.required-demo-' + design_id;	

			pluginsWrapper.find( pluginClassname ).show();
			pluginsWrapper.find( pluginClassname + ' input').prop('disabled', false );

			updatePluginInstallButtons( pluginsForm );
			toggleSortablePluginsNotice();
			pluginsButtonState();

			$builder.on( 'change', function() {

				$( '.wpz-onboard_import-button' ).addClass( 'disabled' );

				pluginsWrapper.find( '[data-plugin-sortable=true]' ).hide();
				pluginsWrapper.find( '[data-plugin-sortable=true] input').prop('disabled', true );
				
				design_id = $(this).data('design-id');
				pluginClassname = '.required-demo-' + design_id;

				pluginsWrapper.find( pluginClassname ).show();
				pluginsWrapper.find( pluginClassname + ' input').prop('disabled', false );

				updatePluginInstallButtons( pluginsForm );
				toggleSortablePluginsNotice();
				pluginsButtonState();
				
			} );
		}

	}

	/**
	 * Handles the demo required only plugins.
	 */
	function demoPluginsSortable() {
		
		const $stepWrap   = $( '.step-choose-design', $wrapper ),
		$designItems      = $( '.wpz-onboard_content-main-step-content form > fieldset > ul > li', $stepWrap );
		pluginsWrapper    = $( '.step-install-plugins', $wrapper );
		pluginsForm       = $( '.wpz-onboard_content-main-step-content form', pluginsWrapper ), 

		//Hide required plugins if demo doesn't need them
		pluginsWrapper.find( '[data-plugin-sortable=true]' ).hide();
		pluginsWrapper.find( '[data-plugin-sortable=true] input' ).prop( 'disabled', true );
		toggleSortablePluginsNotice();

		$designItems.each( function() {

			const $item = $( this );
			const $previewThumbnail = $item.find( 'figure .preview-thumbnail' );
			const design_id         = $item.data( 'design-id' );
			const isSelected = $item.hasClass( 'selected-template' );

			if( isSelected ) {

				pluginClassname = '.required-demo-' + design_id;
				pluginsWrapper.find( pluginClassname ).show();
				pluginsWrapper.find( pluginClassname + ' input' ).prop( 'disabled', false );

				updatePluginInstallButtons( pluginsForm );
				toggleSortablePluginsNotice();
				pluginsButtonState();

			}

			$previewThumbnail.on('click', function( event ) {

				pluginsWrapper.find( '[data-plugin-sortable=true]' ).hide();
				pluginsWrapper.find( '[data-plugin-sortable=true] input').prop('disabled', true );
				event.preventDefault();

				pluginClassname = '.required-demo-' + design_id;
				pluginsWrapper.find( pluginClassname ).show();
				pluginsWrapper.find( pluginClassname + ' input').prop('disabled', false );

				updatePluginInstallButtons( pluginsForm );
				toggleSortablePluginsNotice();
				pluginsButtonState();

			})

		});
	}

	function toggleSortablePluginsNotice() {
		
		noticeWrapper = $( '.wpz-onboard_notice' );
		noticeWrapper.show();
		noticeWrapperPlugins = noticeWrapper.find('[data-plugin-slug]');
		noticeWrapperPlugins.each( function() {
			const $plugin = $(this);
			$plugin.show();
			const $pluginListed = $('#plugins-list').find( '.plugin_' + $plugin.data('plugin-slug') );
			if( $pluginListed.find( 'input' ).is(':disabled') ) {
				$plugin.hide();
			}
		})
		
		$('.wpz-onboard_import-button').addClass( 'disabled' );

		noticePluginReqInstall  = $( '.plugin-level_required[data-notice="notice_can_install_required"] ul' );
		noticePluginReq         = $( '.plugin-level_required[data-notice="notice_can_activate_required"] ul' );
		noticePluginsUp         = $( '.plugin-level_recommended[data-notice="notice_ask_to_update_maybe"] ul' );
		noticePluginsRec        = $( '.plugin-level_recommended[data-notice="notice_can_activate_recommended"] ul' );
		noticePluginsRecInstall = $( '.plugin-level_recommended[data-notice="notice_can_install_recommended"] ul' );

		noticePluginReqInstall.parent().show();
		noticePluginReq.parent().show();
		noticePluginsUp.parent().show();
		noticePluginsRec.parent().show();
		noticePluginsRecInstall.parent().show();

		if( noticePluginReqInstall.children(':visible').length == 0 ) {
			noticePluginReqInstall.parent().hide();
		}
		if( noticePluginReq.children(':visible').length == 0 ) {
			noticePluginReq.parent().hide();
		}
		if( noticePluginsUp.children(':visible').length == 0 ) {
			noticePluginsUp.parent().hide();
		}
		if( noticePluginsRecInstall.children(':visible').length == 0 ) {
			noticePluginsRecInstall.parent().hide();
		}
		if( noticePluginsRec.children(':visible').length == 0 ) {
			noticePluginsRec.parent().hide();
		}

		if( noticeWrapper.find('> ul').children(':visible').length == 0 && 
			noticePluginReq.children(':visible').length == 0 && 
			noticePluginsRec.children(':visible').length == 0 
		) {
			noticeWrapper.hide();
			$('.wpz-onboard_import-button').removeClass( 'disabled' );
		}
		
		$('.wpz-onboard_notice a#wpz-onboard-skip-notice').on('click', function(event) {
			event.preventDefault();
			if( 
				noticePluginReq.children(':visible').length == 0 && 
				noticePluginsRec.children(':visible').length == 0 
			) {
				$('.wpz-onboard_import-button').removeClass( 'disabled' );
				toggleNotice();
				noticeWrapper.hide();
			}
		});

	}

	/**
	 * Callback that is triggered when any of the checkboxes in the plugin install step have their value changed.
	 *
	 * @param {JQuery.ChangeEvent} event The Event object.
	 */
	function onPluginInstallCheckboxChange( event ) {
		event.preventDefault();

		if ( $( this ).is( ':not(:disabled)' ) ) {
			updatePluginInstallButtons( $( this ).closest( 'form' ) );
		}
	}

	/**
	 * Callback that is triggered when the submit button in the plugin install step is clicked.
	 *
	 * @param {JQuery.ClickEvent} event The Event object.
	 */
	function onPluginInstallSubmitClick( event ) {
		event.preventDefault();

		if ( $( this ).is( ':not(:disabled)' ) ) {
			const plugins = [];
			const $form            = $( this ).closest( 'form' ),
			      $fieldset        = $( '> fieldset', $form ),
			      $nonce           = $( 'input[name="wpzoom_required_plugins_nonce"]', $form ),
			      $checkboxes      = $( 'input[name="required_plugins[]"]:not(:disabled):checked', $form ),
			      checkboxesAmount = $checkboxes.length;

			if ( checkboxesAmount > 0 ) {
				$fieldset.attr( 'disabled', true );
				$checkboxes.each( function( index ) {
					const $checkbox = $( this );
					const slug = $checkbox.val();
					plugins.push(slug);
				} );

				if ( plugins.length ) {
					$.ajax( {
						type: 'POST',
						url: ajaxurl,
						data: {
							action: 'wpzoom_required_plugins',
							nonce: $nonce.val(),
							plugins
						},
						beforeSend: () => {
							$.each(plugins, (_, plugin) => {
								const $li = $form.find(`.plugins-list > li.plugin_${plugin}`);
								$li.addClass('plugin-is-installing');
							});
						}
					} ).done((response) => {
						if ( response.success ) {
							const data = response.data || false;
							const error = data && data.error;
							const success = data && data.success;

							if ( ! data ) {
								alert( wpzoomOnboarding.labels.something_wrong );
								return;
							}

							const processItem = (slug, time) => {
								var dfd = $.Deferred();

								setTimeout(() => {
									const $li = $form.find(`.plugins-list > li.plugin_${slug}`);
									const $checkbox = $li.find(`input[type="checkbox"]`);

									// Unconscious mistake.
									if ( typeof $li[0] === 'undefined' ) {
										return;
									}

									$li.removeClass( 'plugin-is-installing' );
									$li[0].className = $li[0].className.replace( /plugin-status_\S+/ig, '' );
									$li[0].className = $li[0].className.replace( /plugin-level_\S+/ig, '' );
									$li.find('.plugin-badge').remove();
									$checkbox.prop( 'disabled', true );

									if ( typeof error[slug] !== 'undefined' ) {
										$li.addClass( 'plugin-status_inactive' );
										$li.find('.plugin-name').append('<span class="plugin-badge">' + error[slug].message + '</span>');
									} else if ( typeof success[slug] !== 'undefined' ) {
										$li.addClass( 'plugin-status_active' );
										$li.find('.plugin-name').append('<span class="plugin-badge">' + wpzoomOnboarding.labels.active + '</span>');

										// Store active plugins.
										activePlugins.push(slug);
									}

									dfd.resolve(slug);
								}, time);

								return dfd.promise();
							}

							const everythingDone = () => {
								$fieldset.attr( 'disabled', false );
		
								/**
								 * Open next pointer.
								 */
								if ( window.hasOwnProperty('wpzoomOnboardingTour') && wpzoomOnboardingTour.currentTarget ) {
									$(window).trigger('wpzoom-onboarding-pointer.defer_loading', wpzoomOnboardingTour.currentIndex + 1);
								}
								
								updatePluginInstallButtons( $form );
								toggleSortablePluginsNotice();
								toggleNotice( data );
								gotoNextStep('#step-import-demo');
							}

							let time = 500;
							const promises = [];
							$.each(plugins, (_, slug) => {
								promises.push(processItem(slug, time));
								time += 500;
							})

							$.when.apply($, promises).then(everythingDone);
						} else {
							const data = response.data || false;
							const msg = data || '[NONE]';
							
							$.when(
								$.each(plugins, (_, slug) => {
									const $li = $form.find(`.plugins-list > li.plugin_${slug}`);
									const $checkbox = $li.find(`input[type="checkbox"]`);

									// Unconscious mistake.
									if ( typeof $li[0] === 'undefined' ) {
										return;
									}

									$li.removeClass( 'plugin-is-installing' );
									$checkbox.prop( 'disabled', false );
								})
							).then(() => {
								setTimeout(() => {
									alert( wpzoomOnboarding.labels.install_failed + msg );
								}, 200)
							})
						}
					})
					.fail(function (jqXHR) {
						$.each(plugins, (_, plugin) => {
							const $li = $form.find(`.plugins-list > li.plugin_${plugin}`);
							$li.removeClass('plugin-is-installing');
						});
						$form.find('.plugins-list').after(`<p class="update-nag notice notice-error" style="display: block; margin-left: 0;">The server responded with a status of ${jqXHR.status} (${jqXHR.statusText}). Reload the page and try again.</p>`);
					})
				}
			}
		}
	}

	function pluginsButtonState() {

		const $step = $wrapper.find( '.step-install-plugins' );
		const $form = $( 'form', $step );
	
		// Check if all visible <li> elements in the list have the class `.plugin-status_active`
		if ( $( '#plugins-list li:visible' ).length > 0 && $( '#plugins-list li:visible' ).length === $( '#plugins-list li:visible.plugin-status_active' ).length ) {
			$( 'input[name="button_submit"]', $form ).attr( 'disabled', true );
			$( 'input[name="button_checkall"]', $form ).attr( 'disabled', true );
		} 
	}
	
	/**
	 * Callback that is triggered when the button to toggle all checkboxes in the plugin install step is clicked.
	 *
	 * @param {JQuery.ClickEvent} event The Event object.
	 */
	function onPluginInstallCheckboxesToggleClick( event ) {
		event.preventDefault();

		const $form = $( this ).closest( 'form' );

		$( 'input[name="required_plugins[]"]:not(:disabled)', $form ).prop( 'checked', !( $( this ).val() == wpzoomOnboarding.labels.check_label_none ) );

		updatePluginInstallButtons( $form );
	}

	/**
	 * Updates the state of the main buttons in the plugin install step.
	 *
	 * @param {JQuery} $form The form where the buttons are located.
	 */
	function updatePluginInstallButtons( $form ) {
		const $checkboxes   = $( 'input[name="required_plugins[]"]:not(:disabled)', $form ),
		      totalAmount   = $checkboxes.length,
		      noCheckboxes  = totalAmount <= 0,
		      checkedAmount = $checkboxes.filter( ':checked' ).length,
		      allChecked    = checkedAmount == totalAmount,
		      someChecked   = checkedAmount > 0,
		      noneChecked   = checkedAmount == 0,
		      checkLabel    = allChecked ? wpzoomOnboarding.labels.check_label_none : wpzoomOnboarding.labels.check_label_all;

		$( 'input[name="button_submit"]', $form ).attr( 'disabled', ( noCheckboxes || noneChecked ) );
		$( 'input[name="button_checkall"]', $form ).val( checkLabel ).attr( 'disabled', noCheckboxes );
	}

	/**
	 * Callback that is triggered when the theme design is changed in the theme design step.
	 *
	 * @param {jQuery} $item The design item which was clicked.
	 * @param {string} design Design to process data for. Meant to be value of selected design.
	 * @param {jQuery} $form Form where the designs are located.
	 * @param {jQuery} $designItems All available design items.
	 */
	function onThemeDesignChange( $item, design, $form, $designItems ) {
		const processTemplateDataDone = (status) => {
			if ( status.success ) {
				const selectedTemplate = $item.find('h5').html();
				$form.closest( '.wpz-onboard_content-main-steps' ).find( '.step-choose-builder' ).show();

				let builderSupport = $item.data('builders-support');

				if( ! builderSupport ) {
					$form.closest( '.wpz-onboard_content-main-steps' ).find( '.step-choose-builder' ).hide();
				}

				if( builderSupport ) {
					let radiosHtml = '';
					Object.keys( builderSupport ).forEach( key => {

						console.log( builderSupport[ key ] );

						let label = key === 'elementor' ? 'Elementor' : 'Block Editor (Gutenberg)';
						let value = `https://www.wpzoom.com/downloads/xml/inspiro-${builderSupport[key]}.xml`;
						let design_name = builderSupport[ key ].name;

						radiosHtml += `
							<radio class="${key}-builder">
								<input type="radio" data-design-name="${design_name}" data-theme-design="${ builderSupport[key].id }" data-design-id="${ builderSupport[key].design_id }" id="${key}" name="builder" value="${value}" ${key === 'elementor' ? 'checked' : ''}>
								<label for="${key}">${label}</label>
							</radio>
						`;
					});

					$form.closest( '.wpz-onboard_content-main-steps' ).find( '.step-choose-builder .wpz-onboard_content-main-step-content' ).html(radiosHtml);

					builderSelectionStep();
					demoPluginsSortableByBuilder();

				}

				// Update the selected template.

				$item.removeClass('preparing-view-template').addClass('selected-template');
				$item.find('.wpz-onboard_dot-elastic').remove();
				$item.find('input[name="theme_designs"]').prop('checked', true);

				if ( status.data.imported ) {
					$item.addClass('imported-demo-content');
					$item.find('.button-select-template').text( wpzoomOnboarding.labels.imported );
				} else {
					$item.find('.button-select-template').text( wpzoomOnboarding.labels.selected );
				}

				$designItems.not($item).removeClass('selected-template imported-demo-content');
				$designItems.not($item).find('input[name="theme_designs"]').prop('checked', false);
				$designItems.not($item).find('.button-select-template').text( wpzoomOnboarding.labels.select_template );

				$form.closest( '.wpz-onboard_content-main-steps' ).find( '.step-import-demo .wpz-onboard_selected-template > strong' ).text( selectedTemplate );
				if( ! builderSupport) {
					gotoNextStep('#step-install-plugins');
				}
				else {
					gotoNextStep('#step-choose-builder');
				}
				

			} else {
				// TODO: add code here when processing template data has error.
			}
		}

		$.when(window.demoImporter.processTemplateData(design)).then(processTemplateDataDone);
	}

	/**
	 * Callback that is triggered when any of the view pages links in the theme design step are clicked.
	 *
	 * @param {string} design Design slug to prepare preview for.
	 * @param {jQuery} $form Form.
	 */
	function onPreparingTemplatePreview( design, $form ) {
		const $nonce = $( 'input[name="wpzoom_theme_design_nonce"]', $form );

		$.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'wpzoom_get_theme_design',
				nonce: $nonce.val(),
				design: design
			}
		} ).done( function( msg ) {
			if ( msg.success && msg.data ) {
				const $contentWrapper = $form.closest( '.wpz-onboard_content-wrapper' ),
					  $contentOverlay = $contentWrapper.find( '.wpz-onboard_content-overlay' ),
					  data            = msg.data;

				let hasNotices = $('.wpz-onboard_notice', $contentWrapper).length > 0;
				const canSkipNotice = $('#wpz-onboard-skip-notice', $contentWrapper).length > 0;
				let currentPointerData, overlayPointer;

				/**
				 * Open content overlay pointer.
				 */
				if ( window.hasOwnProperty('wpzoomOnboardingTour') && wpzoomOnboardingTour.currentTarget ) {
					overlayPointer = wpzoomOnboardingTour.getPointerByPage('content-overlay');
					currentPointerData = wpzoomOnboardingTour.getCurrentData();
					wpzoomOnboardingTour.openNextPointer(currentPointerData, overlayPointer);
				}

				$form.find(`li.design_${design}`).removeClass('preparing-view-template');
				$form.find(`li.design_${design} .wpz-onboard_dot-elastic`).remove();
				if ( $form.find(`li.design_${design}`).hasClass('selected-template') ) {
					$form.find(`li.design_${design} .button-select-template`).text( wpzoomOnboarding.labels.selected );
				} else {
					$form.find(`li.design_${design} .button-select-template`).text( wpzoomOnboarding.labels.select_template );
				}
				
				$( '.wpz-onboard_content-overlay-design-pages-title', $contentOverlay ).text( data.name );
				$( '.wpz-onboard_content-overlay-design-pages-preview-link', $contentOverlay ).attr( 'href', data.preview_url );
				$( '.wpz-onboard_content-overlay-design-import-demo-content', $contentOverlay ).attr( 'data-design-id', design );

				$.each( data.preview_pages, function( index, value ) {
					$( '<li' + ( index == 0 ? ' class="selected"' : '' ) + '><strong>' + value.name + '</strong></li>' )
						.appendTo( '.wpz-onboard_content-overlay-design-pages-thumbs' )
						.css( 'background-image', 'url("' + value.thumbnail + '")' )
						.on( 'click', function( e ) {
							e.preventDefault();

							if ( ! $( this ).hasClass( 'selected' ) ) {
								$( this ).closest( '.wpz-onboard_content-overlay-design-pages-thumbs' ).find( 'li' ).removeClass( 'selected' );
								$( '.wpz-onboard_content-overlay-design-pages-right-pane', $contentOverlay ).html( value.preview_img );
								$( this ).addClass( 'selected' );
							}
						} );
				} );

				$( '.wpz-onboard_content-overlay-design-pages-right-pane', $contentOverlay ).html( data.preview_pages[0].preview_img );

				$( '.go-back-link', $contentOverlay ).on( 'click', function( e ) {
					e.preventDefault();

					$( '.wpz-onboard_content-overlay-design-pages-thumbs', $contentOverlay ).html( '' );
					$( '.wpz-onboard_content-overlay-design-pages-right-pane', $contentOverlay ).html( '' );

					$contentOverlay.removeClass( 'active' );
					$contentWrapper.find( '.wpz-onboard_content' ).addClass( 'active' );

					gotoNextStep('#step-choose-design');

					/**
					 * Open previous pointer.
					 */
					if ( currentPointerData && overlayPointer ) {
						wpzoomOnboardingTour.openNextPointer(overlayPointer, currentPointerData);
					}
				} );

				$('.wpz-onboard_content-overlay-design-import-demo-content', $contentOverlay).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					const designId = $(this).attr('data-design-id');
					const triggerImportContent = () => {
						if ( canSkipNotice ) {
							// Skip recommended notice.
							$('#wpz-onboard-skip-notice', $contentWrapper).trigger('click');
						} else {
							$(document).trigger( 'wpzoom-onboard-skip-notice' );
						}
					}

					$('.wpz-onboard_import-button', $contentWrapper).attr('data-processing-design', designId);
					$( '.wpz-onboard_content-overlay-design-pages-thumbs', $contentOverlay ).html( '' );
					$( '.wpz-onboard_content-overlay-design-pages-right-pane > img', $contentOverlay ).attr( 'src', '' );

					$contentOverlay.removeClass( 'active' );
					$contentWrapper.find( '.wpz-onboard_content' ).addClass( 'active' );

					gotoNextStep('#step-import-demo');

					// Check to select design and prepare template data.
					if ( ! $form.find(`li.design_${designId}`).hasClass('selected-template') ) {
						$form.find(`li.design_${designId} figure .preview-thumbnail`).trigger('click');
					} else {
						triggerImportContent();
					}

					$('input[name="wpzoom-wxr-url"]', $contentWrapper).bind('change', function(){
						triggerImportContent();
					});
				});

				$(document).on('wpzoom-onboard-skip-notice', () => {
					const isDesignProcess = $('.wpz-onboard_import-button', $contentWrapper).attr('data-processing-design');
					hasNotices = $('.wpz-onboard_notice', $contentWrapper).length > 0;

					// Remove events listener.
					$(document).off('wpzoom-onboard-skip-notice');
					$('.wpz-onboard_content-overlay-design-import-demo-content', $contentOverlay).off('click');
					$('#wpz-onboard-skip-notice', $contentWrapper).off('click');
					$('.wpz-onboard_import-button', $contentWrapper).off('click');
					$('input[name="wpzoom-wxr-url"]', $contentWrapper).off('change');

					// Trigger Import demo content button.
					if ( isDesignProcess && ! hasNotices ) {
						$('.wpz-onboard_import-button', $contentWrapper).removeAttr('data-processing-design');
						$('.wpz-onboard_import-button', $contentWrapper).trigger('click');

						if ( window.hasOwnProperty('wpzoomOnboardingTour') ) {
							wpzoomOnboardingTour.destroyPointers();
							wpzoomOnboardingTour.dismissPointer();
						}
					}

					gotoNextStep('#step-install-plugins');
				});

				$contentWrapper.find( '.wpz-onboard_content' ).removeClass( 'active' );
				$contentOverlay.addClass( 'active' );

				$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );
			}
		} );
	}

	/**
	 * Callback that is triggered when the load button in the demo content import step is clicked.
	 *
	 * @param {JQuery.ClickEvent} event The Event object.
	 */
	function onDemoImportLoadClick( event ) {
		event.preventDefault();

		if ( ! $( this ).hasClass( 'disabled' ) ) {
			window.demoImporter.openSelectDemoModal();
		}
	}

	/**
	 * Scroll animation after step is done.
	 * 
	 * @param {string} hash The hash id of section to scroll for.
	 */
	function gotoNextStep(hash) {
		$('html, body').animate({
			scrollTop: $(hash).offset().top
		}, 800, function(){
	
			// Add hash (#) to URL when done scrolling (default click behavior)
			window.location.hash = hash;
		});
	}

	/**
	 * Toggle notice if all required plugins are active.
	 * 
	 * @param {bool|object} data AJAX response data or custom data value.
	 * @param {string} elementId DOM element id. Default: 'step-import-demo'.
	 */
	function toggleNotice( data = false, elementId = 'step-import-demo' ) {
		const compatibilities = wpzoomOnboarding.compatibilities;
		const $el = $(document).find(`#${elementId}`);
		
		if ( ! $el.length ) {
			return;
		}

		const $noticeWrapper = $el.find('.wpz-onboard_notice');

		const removeNoticeBox = ($wrapper = '') => {
			$wrapper = $wrapper || $noticeWrapper;
			if ( $wrapper.find('> ul').is(':empty') ) {
				$wrapper.remove();
			}

			if ( $noticeWrapper.find('> ul').is(':empty') ) {
				$noticeWrapper.remove();
				$el.find('.wpz-onboard_import-button').removeClass('disabled');
				$(document).trigger('wpzoom-onboard-remove-notice');
			}
		}

		const rebuildNoticeBox = ( notice, noticeData ) => {
			if ( ! noticeData.title || ! noticeData.description ) {
				return;
			}
			if ( $noticeWrapper.length ) {
				$el.find(`li[data-notice="${notice}"]`).remove();
				$el.find('.wpz-onboard_notice > ul').prepend(`<li class="plugin-level_required" data-notice="${notice}"><h3>${noticeData.title}<span class="plugin-badge">Required</span></h3>${noticeData.description}</li>`);
			} else {
				$el.find('.wpz-onboard_content-main-step-content').prepend('<div class="wpz-onboard_notice"><ul></ul></div>');
				$el.find('.wpz-onboard_notice > ul').prepend(`<li class="plugin-level_required" data-notice="${notice}"><h3>${noticeData.title}<span class="plugin-badge">Required</span></h3>${noticeData.description}</li>`);
			}
		}

		const updateCompatibilities = ( notice, type = 'errors' ) => {
			if ( wpzoomOnboarding.compatibilities[ type ] && wpzoomOnboarding.compatibilities[ type ][ notice ] ) {
				delete wpzoomOnboarding.compatibilities[ type ][ notice ];
			}
		}

		// Loop errors.
		for (const notice in compatibilities.errors) {
			const $noticeItem = $noticeWrapper.find(`ul li[data-notice="${notice}"]`);
			const plugins = $noticeItem.find('li[data-plugin-slug]');

			if ( notice === 'notice_can_activate_required' || notice === 'notice_can_install_required' ) {
				for (const plugin of plugins) {
					const slug = plugin.getAttribute('data-plugin-slug');

					// Check plugin is activated to remove from notice warning.
					if ( activePlugins.length && activePlugins.includes(slug) ) {
						const $pluginElement = $noticeItem.find(`li[data-plugin-slug="${slug}"]`);
						if ($pluginElement.length) {
							$pluginElement.fadeOut('100', function(){
								$pluginElement.remove();
								removeNoticeBox( $noticeItem );
								updateCompatibilities( notice );
							});
						}
					}
				}
			}

			if( notice === 'woocomerce-enabled' ) {
				$el.find('.wpz-onboard_import-button').addClass('disabled');
				updateCompatibilities( notice );
			}

			if ( notice === 'empty_license_key' || notice === 'inactive_license_key' || notice === 'expired_license_key' ) {
				if ( typeof data === 'object' && data.licenseStatus ) {
					const noticeData = ( data.notice && data.notice[ notice ] ) || compatibilities.errors[ notice ];
					const licenseStatus = data.licenseStatus;
					
					if ( 'valid' === licenseStatus ) {
						$noticeItem.remove();
						removeNoticeBox();
						updateCompatibilities( notice );
					} else {
						$el.find('.wpz-onboard_import-button').addClass('disabled');
						rebuildNoticeBox( notice, noticeData );
						updateCompatibilities( notice );
					}
				}
			}
		}

		// Loop warnings.
		for (const notice in compatibilities.warnings) {
			const $noticeItem = $noticeWrapper.find(`ul li[data-notice="${notice}"]`);
			const plugins = $noticeItem.find('li[data-plugin-slug]');

			if ( notice === 'notice_can_activate_recommended' || notice === 'notice_can_install_recommended' ) {
				for (const plugin of plugins) {
					const slug = plugin.getAttribute('data-plugin-slug');

					// Check plugin is activated to remove from notice warning.
					if ( activePlugins.length && activePlugins.includes(slug) ) {
						const $pluginElement = $noticeItem.find(`li[data-plugin-slug="${slug}"]`);
						if ($pluginElement.length) {
							$pluginElement.fadeOut('100', function(){
								$pluginElement.remove();
								removeNoticeBox( $noticeItem );
								updateCompatibilities( notice, 'warnings' );
							});	
						}
					}
				}
			}
		}

		// Skip recommended notice.
		if ( ! data ) {
			const $recommended = $noticeWrapper.find('ul li.plugin-level_recommended');
			$recommended.fadeOut('100', function(){
				$recommended.remove();
				removeNoticeBox();
				$(document).trigger('wpzoom-onboard-skip-notice');

				/**
				 * Open next pointer.
				 */
				if ( window.hasOwnProperty('wpzoomOnboardingTour') && wpzoomOnboardingTour.currentTarget ) {
					const currentData = wpzoomOnboardingTour.getNextData(wpzoomOnboardingTour.currentIndex);
					let index = wpzoomOnboardingTour.currentIndex + 1;

					wpzoomOnboardingTour.createPointer(currentData, index);

					if ( ! wpzoomOnboardingTour.hasSelectorInDOM(currentData.selector) ) {
						// Go to next pointer if selector was not found in DOM.
						const nextData = wpzoomOnboardingTour.getNextData(index);
						wpzoomOnboardingTour.createPointer(nextData, index, true);
						index = wpzoomOnboardingTour.getPointer(nextData, true);
					}

					$(window).trigger('wpzoom-onboarding-pointer.defer_loading', index );
				}
			});
		}
	}

	function activateLicenseKey() {
		const $licenseTabPanel = $wrapper.find('.wpz-onboard_content-main .wpz-onboard_content-main-license');
		const $form = $licenseTabPanel.find('form');
		const $licenseInput = $form.find('input[name="license_key"]');
		const $licenseLabel = $form.find('label[for="wpzoom_license_key"]');
		const prevLicenseKey = $licenseInput.val();

		$form.on('click', '.wpz-onboard_content-main-license-submit', function(event){
			event.preventDefault();
			const $saveActivateButton = $(this).parent().find('input.button-primary');
			const $licenseNote = $saveActivateButton.closest('fieldset').find('.wpz-onboard_content-main-license-note');
			const $licenseTabItem = $wrapper.find('#wpz-onboard_tabs .wpz-onboard_tab-license');
			const nonce = $form.find('input[name="wpzoom_set_license_nonce"]').val();
			const licenseKey = $licenseInput.val();
			const prevLicenseNote = $licenseNote.html();
			let actionName = $form.find('input[name="action"]').val();

			if (typeof $(this).attr('data-deactivate-action') !== 'undefined') {
				actionName = $(this).attr('data-deactivate-action');
			}

			$.ajax( {
				type: 'POST',
				url: ajaxurl,
				data: {
					action: actionName,
					license: licenseKey,
					prev_license_key: prevLicenseKey,
					nonce,
				},
				beforeSend: () => {
					$(this).attr('disabled', true);
					if ( actionName === 'wpzoom_set_license' ) {
						$licenseNote.text(wpzoomOnboarding.labels.saving_license);
					} else if ( actionName === 'wpzoom_deactivate_license' ) {
						$licenseNote.text(wpzoomOnboarding.labels.deactivating_license);
					}
				}
			} ).done((response) => {
				const data = response.data;
				const message = data.message || wpzoomOnboarding.labels.something_wrong_license;
				const licenseStatus = data.status || 'invalid';
				const deactivateBtn = `<input type="submit" value="${wpzoomOnboarding.labels.deactivate_license}" class="wpz-onboard_content-main-license-submit button button-secondary" data-deactivate-action="wpzoom_deactivate_license">`;
				const $deactivateButton = $form.find('input[data-deactivate-action="wpzoom_deactivate_license"]');

				// Merge global variable 'wpzoomOnboarding.compatibilities' with received notice object from AJAX response.
				if ( data.notice && typeof data.notice === 'object' ) {
					wpzoomOnboarding.compatibilities.errors = { ...data.notice, ...wpzoomOnboarding.compatibilities.errors };
				}

				$(this).attr('disabled', false);
				$licenseInput.attr('data-license-status', licenseStatus);

				if ( ! $licenseLabel.find('.label-badge').length > 0 ) {
					$licenseLabel.append('<span class="label-badge"></span>');
				}
				if ( data.label ) {
					$licenseLabel.find('.label-badge').attr('data-license-status', licenseStatus).text(data.label).show();
				} else {
					$licenseLabel.find('.label-badge').attr('data-license-status', licenseStatus).hide();
				}

				if ( response.success ) {
					if ( actionName === 'wpzoom_set_license' ) {
						if ( ! $deactivateButton.length ) {
							$saveActivateButton.attr('disabled', true).after(deactivateBtn);
						}
						$licenseTabItem.find('span[class^="license-status"]').remove();
					} else if ( actionName === 'wpzoom_deactivate_license' ) {
						$saveActivateButton.attr('disabled', false);
						$deactivateButton.remove();
						$licenseTabItem.find('> a').append(`<span class="license-status-${licenseStatus}-badge"></span>`);
					}
					$licenseNote.html(message);
				} else {
					if ( actionName === 'wpzoom_deactivate_license' && prevLicenseKey !== licenseKey ) {
						$licenseInput.val(prevLicenseKey);
						$licenseNote.html(prevLicenseNote);
						$saveActivateButton.attr('disabled', true);
					} else {
						$saveActivateButton.attr('disabled', false);
						$deactivateButton.remove();
						$licenseNote.html(message);
					}
					$licenseTabItem.find('> a').append(`<span class="license-status-${licenseStatus}-badge"></span>`);
				}

				// Display quick start tab if license was activated successfully.
				// if ( 'valid' === licenseStatus ) {
				// 	setTimeout( () => {
				// 		setTab( '#quick-start', true );
				// 	}, 500 );
				// }

				toggleNotice( {
					licenseStatus,
					licenseKey,
					notice: data.notice
				} );
			});
		});

		// Watch for input value changes to enable "Save & Activate" button.
		$form.on('input', 'input[name="license_key"]', function() {
			const $input = $(this);
			const $saveActivateButton = $input.parent().find('input.button-primary');
			const $deactivateButton = $input.parent().find('input[data-deactivate-action="wpzoom_deactivate_license"]');
			const licenseStatus = $input.attr('data-license-status');
			const inputValue = $input.val();

			if ( $saveActivateButton.prop('disabled') ) {
				$saveActivateButton.attr('disabled', false);
			} else if ( prevLicenseKey === inputValue && $deactivateButton.length && licenseStatus === 'valid' ) {
				$saveActivateButton.attr('disabled', true);
			}
		});
	}

	$( '.wpz-onboard_filter-designs a' ).on( 'click', function( event ) {
		event.preventDefault();

		const $this = $( this );

		//Active class
		$('.wpz-onboard_filter-designs a').removeClass('active');
		$this.addClass('active');

		var filter = $(this).data('filter'); // Get the filter value

		var $allDemos = $(".wpz-onboard_content-main-step-content ul li[data-design-id]");
		var blockDemos = $(".wpz-onboard_content-main-step-content ul li[data-design-id].is-block-design");

		if ( filter === 'all' ) {
			$allDemos.find('.supported-by img').show();
			$allDemos.not( '.selected-template' ).filter('.hidden').removeClass('fade-in fade-out');
			$allDemos.not('.hidden').removeClass('fade-out').addClass('fade-in');
		} else if ( filter === 'elementor' ) {
			$allDemos.find('.supported-by img.wpzoom-icon-gutenberg').hide();
			$allDemos.find('.supported-by img.wpzoom-icon-elementor').show();
			$allDemos.removeClass('fade-out').addClass('fade-in');
			blockDemos.addClass('fade-out');
		} else if ( filter === 'block' ) {
			blockDemos.find('.supported-by img.wpzoom-icon-gutenberg').show();
			blockDemos.find('.supported-by img.wpzoom-icon-elementor').hide();
			$allDemos.addClass('fade-out'); 
			blockDemos.removeClass('fade-out').addClass('fade-in');
		}
		

	} );

} )( jQuery );

document.addEventListener("DOMContentLoaded", function() {
    const lazyBackgrounds = document.querySelectorAll(".preview-thumbnail");

    const lazyLoad = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const bg = entry.target.getAttribute("data-bg");
                if (bg) {
                    entry.target.style.backgroundImage = `url('${bg}')`;
                    entry.target.removeAttribute("data-bg"); // Remove to prevent reloading
                }
                observer.unobserve(entry.target);
            }
        });
    };

    const observer = new IntersectionObserver(lazyLoad, {
        rootMargin: "100px",
        threshold: 0.1
    });

    lazyBackgrounds.forEach(bg => observer.observe(bg));
});
