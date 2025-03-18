<?php
/**
 * WordPress Importer
 *
 * @package WPZOOM
 *
 * WordPress Importer
 * https://github.com/humanmade/WordPress-Importer
 *
 * Released under the GNU General Public License v2.0
 * https://github.com/humanmade/WordPress-Importer/blob/master/LICENSE
 */

/**
 * WPZOOM Importer extends WXR Importer
 */
if ( ! class_exists( 'WPZOOM_WXR_Importer' ) && class_exists( 'WXR_Importer' ) ) :

	/**
	 * WPZOOM Importer extends WXR Importer
	 *
	 * @since 2.0.0
	 */
	class WPZOOM_WXR_Importer extends WXR_Importer {

		/**
		 * Processed Attachments
		 *
		 * @var array
		 */
		public static $processed_attachments = array();

		/**
		 * Parses the WXR file and prepares us for the task of processing parsed data
		 *
		 * @param string $file Path to the WXR file for importing.
		 */
		protected function import_start( $file ) {
			if ( ! is_file( $file ) ) {
				return new WP_Error( 'wxr_importer.file_missing', __( 'The file does not exist, please try again.', 'wordpress-importer' ) );
			}

			// Suspend bunches of stuff in WP core.
			wp_defer_term_counting( true );
			wp_defer_comment_counting( true );
			wp_suspend_cache_invalidation( true );

			// Prefill exists calls if told to.
			if ( $this->options['prefill_existing_posts'] ) {
				$this->prefill_existing_posts();
			}
			if ( $this->options['prefill_existing_comments'] ) {
				$this->prefill_existing_comments();
			}
			if ( $this->options['prefill_existing_terms'] ) {
				$this->prefill_existing_terms();
			}

			/**
			 * Begin the import.
			 *
			 * Fires before the import process has begun. If you need to suspend
			 * caching or heavy processing on hooks, do so here.
			 */
			do_action( 'import_start' );
		}

		/**
		 * Get processed attachment data
		 *
		 * @since 2.0.0
		 * @param int $attachment_id The attachment id to get processed data for.
		 * @return mixed Processed attachment or false if attachment id not found in $processed_attachments array.
		 */
		public static function get_processed_attachment_data( $attachment_id ) {
			if ( isset( self::$processed_attachments[ $attachment_id ] ) ) {
				return self::$processed_attachments[ $attachment_id ];
			}
			return false;
		}

		/**
		 * Parse Nav Menu Node
		 *
		 * @param  object $node Nav Menu Node.
		 * @return array
		 */
		protected function parse_nav_menu_node( $node ) {
			$data = array();

			foreach ( $node->childNodes as $child ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				// We only care about child elements.
				if ( XML_ELEMENT_NODE !== $child->nodeType ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					continue;
				}

				switch ( $child->tagName ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					case 'wp:term_id':
						$data['id'] = $child->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						break;

					case 'wp:term_taxonomy':
						$data['type'] = $child->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						break;

					case 'wp:term_slug':
						$data['slug'] = $child->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						break;

					case 'wp:term_name':
						$data['name'] = $child->textContent; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						break;
				}
			}

			return $data;
		}

		/**
		 * Parse template options based on XML file.
		 *
		 * @param string $file Downloaded XML file absolute URL.
		 * @return object Converted template options from XML file.
		 */
		public function get_template_options_based_on_xml( $file ) {
			// Let's run the actual importer now, woot.
			$reader = $this->get_reader( $file );
			if ( is_wp_error( $reader ) ) {
				return $reader;
			}

			// Start parsing!
			$data = new stdClass();
			while ( $reader->read() ) {
				// Only deal with element opens.
				if ( XMLReader::ELEMENT !== $reader->nodeType ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					continue;
				}

				// Remove 'wpzoom' prefix from theme raw name.
				$theme_slug = str_replace( 'wpzoom-', '', WPZOOM::$theme_raw_name );

				switch ( $reader->name ) {
					case 'item':
						$node   = $reader->expand();
						$parsed = $this->parse_post_node( $node );
						if ( is_wp_error( $parsed ) ) {
							$this->log_error( $parsed );

							// Skip the rest of this post.
							$reader->next();
							break;
						}

						// TODO: Refactor below code because now is too risky to set page for front and blog.

						// Set "Homepage" page as 'page_on_front'.
						if ( 'page' === $parsed['data']['post_type'] && ( 'homepage-' . $theme_slug ) === $parsed['data']['post_name'] ) {
							$data->page_on_front = $parsed['data']['post_title'];
							$data->show_on_front = 'page';
						}

						// Set "Blog" page as 'page_for_posts'.
						if ( 'page' === $parsed['data']['post_type'] && ( 'blog-' . $theme_slug ) === $parsed['data']['post_name'] ) {
							$data->page_for_posts = $parsed['data']['post_title'];
						}

						// Handled everything in this node, move on to the next.
						$reader->next();
						break;

					case 'wp:term':
						$node = $reader->expand();

						if ( strpos( $node->nodeValue, 'nav_menu' ) !== false ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$parsed = $this->parse_nav_menu_node( $node );
							if ( is_wp_error( $parsed ) ) {
								$this->log_error( $parsed );

								// Skip the rest of this post.
								$reader->next();
								break;
							}

							$data->nav_menus[] = $parsed;
						}

						// Handled everything in this node, move on to the next.
						$reader->next();
						break;
				}
			}

			if ( isset( $data->nav_menus ) ) {
				$locations = get_registered_nav_menus();
				foreach ( $locations as $location => $description ) {
					foreach ( $data->nav_menus as $nav_menu ) {
						// Check by slug menu '{location}-{theme-slug}' to set for menu location.
						$menu_slug = $location . '-' . $theme_slug;

						if ( strpos( $nav_menu['slug'], $menu_slug ) === 0 ) {
							$data->nav_menu_locations[ $location ] = $nav_menu['slug'];
						}
					}
				}
			}

			return $data;
		}

		/**
		 * The main controller for the actual import stage.
		 *
		 * @param string $file Path to the WXR file for importing.
		 */
		public function get_preliminary_information( $file ) {
			// Let's run the actual importer now, woot.
			$reader = $this->get_reader( $file );
			if ( is_wp_error( $reader ) ) {
				return $reader;
			}

			// Set the version to compatibility mode first.
			$this->version = '1.0';

			// Start parsing!
			$data = new WPZOOM_WXR_Import_Info();
			while ( $reader->read() ) {
				// Only deal with element opens.
				if ( XMLReader::ELEMENT !== $reader->nodeType ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					continue;
				}

				switch ( $reader->name ) {
					case 'wp:wxr_version':
						// Upgrade to the correct version.
						$this->version = $reader->readString();

						if ( version_compare( $this->version, self::MAX_WXR_VERSION, '>' ) ) {
							$this->logger->warning(
								sprintf(
									/* translators: %1$s is WXR version, %2$s is max supported WXR version. */
									__( 'This WXR file (version %1$s) is newer than the importer (version %2$s) and may not be supported. Please consider updating.', 'wordpress-importer' ),
									$this->version,
									self::MAX_WXR_VERSION
								)
							);
						}

						// Handled everything in this node, move on to the next.
						$reader->next();
						break;

					case 'generator':
						$data->generator = $reader->readString();
						$reader->next();
						break;

					case 'title':
						$data->title = $reader->readString();
						$reader->next();
						break;

					case 'wp:base_site_url':
						$data->siteurl = $reader->readString();
						$reader->next();
						break;

					case 'wp:base_blog_url':
						$data->home = $reader->readString();
						$reader->next();
						break;

					case 'wp:author':
						$node = $reader->expand();

						$parsed = $this->parse_author_node( $node );
						if ( is_wp_error( $parsed ) ) {
							$this->log_error( $parsed );

							// Skip the rest of this post.
							$reader->next();
							break;
						}

						$data->users[] = $parsed;

						if ( ! in_array( 'users', $data->sort_data ) ) {
							$data->sort_data[] = 'users';
						}

						// Handled everything in this node, move on to the next.
						$reader->next();
						break;

					case 'item':
						$node   = $reader->expand();
						$parsed = $this->parse_post_node( $node );
						if ( is_wp_error( $parsed ) ) {
							$this->log_error( $parsed );

							// Skip the rest of this post.
							$reader->next();
							break;
						}

						if ( 'attachment' === $parsed['data']['post_type'] ) {
							$type = 'media';
							$data->media_count++;
						} elseif ( 'nav_menu_item' === $parsed['data']['post_type'] ) {
							$type = 'nav_menu_items';
							$data->nav_menu_count++;
						} elseif ( 'page' === $parsed['data']['post_type'] ) {
							$type = 'pages';
							$data->page_count++;
						} elseif ( 'portfolio_item' === $parsed['data']['post_type'] ) {
							$type = 'portfolios';
							$data->portfolio_count++;
						} elseif ( 'post' === $parsed['data']['post_type'] ) {
							$type = 'posts';
							$data->post_count++;
						} else {
							$type = 'other';
							$data->other_count++;
						}
						$data->comment_count += count( $parsed['comments'] );

						if ( ! in_array( $type, $data->sort_data ) ) {
							$data->sort_data[] = $type;
						}

						// Handled everything in this node, move on to the next.
						$reader->next();
						break;

					case 'wp:category':
					case 'wp:tag':
					case 'wp:term':
						$data->term_count++;

						if ( ! in_array( 'terms', $data->sort_data ) ) {
							$data->sort_data[] = 'terms';
						}

						// Handled everything in this node, move on to the next.
						$reader->next();
						break;
				}
			}

			$data->version = $this->version;

			return $data;
		}

		/**
		 * If fetching attachments is enabled then attempt to create a new attachment
		 *
		 * @param array  $post Attachment post details from WXR.
		 * @param string $meta Raw meta data, already processed by {@see process_post_meta}.
		 * @param string $remote_url URL to fetch attachment from.
		 * @return int|WP_Error Post ID on success, WP_Error otherwise.
		 */
		protected function process_attachment( $post, $meta, $remote_url ) {
			// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
			// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload().
			$post['upload_date'] = $post['post_date'];
			foreach ( $meta as $meta_item ) {
				if ( '_wp_attached_file' !== $meta_item['key'] ) {
					continue;
				}

				if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta_item['value'], $matches ) ) {
					$post['upload_date'] = $matches[0];
				}
				break;
			}

			// if the URL is absolute, but does not contain address, then upload it assuming base_site_url.
			if ( preg_match( '|^/[\w\W]+$|', $remote_url ) ) {
				$remote_url = rtrim( $this->base_url, '/' ) . $remote_url;
			}

			$upload = $this->fetch_remote_file( $remote_url, $post );
			if ( is_wp_error( $upload ) ) {
				return $upload;
			}

			$info = wp_check_filetype( $upload['file'] );
			if ( ! $info ) {
				return new WP_Error( 'attachment_processing_error', __( 'Invalid file type', 'wordpress-importer' ) );
			}

			$post['post_mime_type'] = $info['type'];

			// WP really likes using the GUID for display. Allow updating it.
			// See https://core.trac.wordpress.org/ticket/33386.
			if ( $this->options['update_attachment_guids'] ) {
				$post['guid'] = $upload['url'];
			}

			$post_id = wp_insert_attachment( $post, $upload['file'] );
			if ( is_wp_error( $post_id ) ) {
				return $post_id;
			}

			// Pass attachment upload data.
			self::$processed_attachments[ $post_id ] = $upload;

			// Map this image URL later if we need to.
			$this->url_remap[ $remote_url ] = $upload['url'];

			// If we have a HTTPS URL, ensure the HTTP URL gets replaced too.
			if ( substr( $remote_url, 0, 8 ) === 'https://' ) {
				$insecure_url                     = 'http' . substr( $remote_url, 5 );
				$this->url_remap[ $insecure_url ] = $upload['url'];
			}

			return $post_id;
		}

		/**
		 * Attempt to download a remote file attachment
		 *
		 * @param string $url URL of item to fetch.
		 * @param array  $post Attachment details.
		 * @return array|WP_Error Local file location details on success, WP_Error otherwise.
		 */
		protected function fetch_remote_file( $url, $post ) {
			// extract the file name and extension from the url.
			$file_name = basename( $url );

			// get placeholder file in the upload dir with a unique, sanitized filename.
			$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] ); // phpcs:ignore WordPress.WP.DeprecatedParameters.Wp_upload_bitsParam2Found
			if ( $upload['error'] ) {
				return new WP_Error( 'upload_dir_error', $upload['error'] );
			}

			// fetch the remote url and write it to the placeholder file.
			$response = wp_remote_get(
				$url,
				array(
					'stream'   => true,
					'filename' => $upload['file'],
				)
			);

			// request failed.
			if ( is_wp_error( $response ) ) {
				unlink( $upload['file'] );
				return $response;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );

			// make sure the fetch was successful.
			if ( 200 !== $code ) {
				unlink( $upload['file'] );
				return new WP_Error(
					'import_file_error',
					sprintf(
						/* translators: %1$s is error code, %2$s is error code header, %3$s is url. */
						__( 'Remote server returned %1$d %2$s for %3$s', 'wordpress-importer' ),
						$code,
						get_status_header_desc( $code ),
						$url
					)
				);
			}

			$filesize = filesize( $upload['file'] );
			$headers  = wp_remote_retrieve_headers( $response );

			if ( isset( $headers['content-length'] ) && $filesize !== (int) $headers['content-length'] ) {
				unlink( $upload['file'] );
				return new WP_Error( 'import_file_error', __( 'Remote file is incorrect size', 'wordpress-importer' ) );
			}

			if ( 0 === $filesize ) {
				unlink( $upload['file'] );
				return new WP_Error( 'import_file_error', __( 'Zero size file downloaded', 'wordpress-importer' ) );
			}

			$max_size = (int) $this->max_attachment_size();
			if ( ! empty( $max_size ) && $filesize > $max_size ) {
				unlink( $upload['file'] );
				/* translators: %s max file size. */
				$message = sprintf( __( 'Remote file is too large, limit is %s', 'wordpress-importer' ), size_format( $max_size ) );
				return new WP_Error( 'import_file_error', $message );
			}

			return $upload;
		}

	}
endif;
