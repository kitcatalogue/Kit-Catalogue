<?php
/**
 * A class providing Creole 1.0 Wiki-code parsing.
 *
 * @package  Ecl
 * @version  1.1.0
 */
class Ecl_Parser_Wikicode {

	private $_escape_id = null;
	private $_escaped_sections = null;

	private $_lines = array();
	private $_lines_count = 0;



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Parse wiki-code text into XHTML
	 *
	 * @param  string  $wiki_string  The string to parse.
	 *
	 * @return  string  The resultant XHTML string.
	 */
	public function parse($wiki_string) {
		$output = null;

		// Convert Windows and Mac new line codes to just single \n (newline) chars
		$wiki_string = str_replace("\r\n", "\n", $wiki_string);
		$wiki_string = str_replace("\r", "\n", $wiki_string);

		// End the wiki string with a new-line to ensure the closing of
		// any active formatting codes at the end of the text
		$wiki_string .= "\n";

		$wiki_string = $this->_escapeWikiString($wiki_string);

		// Parse block level wiki-code
		$this->_lines = explode("\n", $wiki_string);
		$this->_lines_count = count($this->_lines);

		if ($this->_lines_count>0) {

			$i = 0;
			while($i<$this->_lines_count) {
				$line = $this->_getLine($i);

				$first_char = substr($line, 0, 1);

				if ($first_char !== false) {
					switch ($first_char) {
						case '-' :   // Horizontal Line
							$output .= $this->_parseHorizontalLine($i);
							break;
						// ----------
						case '=' :   // Heading
							$output .= $this->_parseHeading($i);
							break;
						// ----------
						case '*' :   // List
						case '#' :
							$output .= $this->_parseList($i);
							break;
						// ----------
						case '|' :   // Table
							$output .= $this->_parseTable($i);
							break;
						// ----------
						default :
							$output .= $this->_parseParagraph($i);
							break;
					}
				}
				$i++;
			}// /while(lines to process)

		}// /if(lines);

		$output = $this->_unescapeWikiString($output);

		return $output;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Get a line from the wiki string
	 *
	 * @param  integer  $index  The index of the line to fetch (0-based).
	 *
	 * @return  string  The string
	 */
	private function _getLine($index) {
		if ($index<$this->_lines_count) {
			return trim($this->_lines[$index]);
		} else {
			return null;
		}
	}// /method



	/**
	 * Escape any special sections/chars in the wiki string.
	 *
	 * @param  string  $string  The string to parse.
	 *
	 * @return  mixed  The string produced.  On fail, null.
	 */
	private function _escapeWikiString($string) {
		$this->_escape_id = ''. mt_rand(1, 9999);
		$this->_escaped_sections = null;

		$all_matches = null;

		$count = preg_match_all('%{{{\n(.*)\n}}}%sU', $string, $matches);
		if ($count>=1) {
			for($i=0; $i<$count; $i++) {
				$all_matches[0][] = $matches[0][$i];
				$all_matches[1][] = "<pre>\n".$matches[1][$i]."\n</pre>";
			}
		}

		$count = preg_match_all('%{{{(.*)}}}%sU', $string, $matches);
		if ($count>=1) {
			for($i=0; $i<$count; $i++) {
				$all_matches[0][] = $matches[0][$i];
				$all_matches[1][] = $matches[1][$i];
			}
		}

		$count = preg_match_all('%(?<=\s)~(.)%', $string, $matches);
		if ($count>=1) {
			for($i=0; $i<$count; $i++) {
				$all_matches[0][] = $matches[0][$i];
				$all_matches[1][] = $matches[1][$i];
			}
		}

		if (is_array($all_matches)) {
			$count = count($all_matches[1]);

			for($i=0; $i<$count; $i++) {
				$this->_escaped_sections[$i] = $all_matches[1][$i];
				$escape_block = "??ESC-{$this->_escape_id}-{$i}??";
				$string = str_replace($all_matches[0][$i], $escape_block, $string);
			}
		}
		return $string;
	}// /method



	/**
	 * Parse a single line as a Heading.
	 *
	 * @param  string  $string  The string to parse.
	 *
	 * @return  mixed  The string produced.  On fail, null.
	 */
	private function _parseHeading(&$line_idx) {
		$output = null;

		$line = $this->_getLine($line_idx);

		// Look for the = and capture as well as the heading text
		preg_match('%^(={1,6}) *(.*?) *=*$%', $line, $matches);

		// If we have matches (which we should have!)
		if (is_array($matches)) {
			$level = strlen($matches[1]);
			$output = "<h{$level}>{$matches[2]}</h{$level}>\n\n";
		}
		return $output;
	}// /method



	/**
	 * Parse a horizontal line.
	 *
	 * @param  string  $string  The string to parse.
	 *
	 * @return  mixed  The string produced.  On fail, null.
	 */
	private function _parseHorizontalLine(&$line_idx) {
		$output = null;

		if (preg_match('%^[-]{4}$%U', $this->_getLine($line_idx))) {
			$output = "<hr />\n\n";
		}

		return $output;
	}// /method



	/**
	 * Parse the inline wiki elements.
	 *
	 * @param  string  $wiki_string  The string to process.
	 *
	 * @return  mixed  The string produced.  On fail, null.
	 */
	private function _parseInline($wiki_string) {

		$output = trim($wiki_string);

		// Force Newline
		$output = preg_replace('%([\\\]{2})%sU', ' <br /> ', $output);

		// Links
		$output = $this->_parseInlineLinks($output);

		// Images
		$output = $this->_parseInlineImages($output);

		// Bold
		$output = preg_replace('%\*\*(.*)(?:\*\*|$)%sU', '<strong>$1</strong>', $output);
		$output = preg_replace('%__(.*)(?:__|$)%sU', '<strong>$1</strong>', $output);

		// Italic
		$output = preg_replace('%(?<!http:|https:|ftp:)//(.*)(?<!http:|https:|ftp:)//%sU', '<em>$1</em>', $output);
		$output = preg_replace('%(?<!http:|https:|ftp:)//(.*)(?://|$)%sU', '<em>$1</em>', $output);
		$output = preg_replace('%\'\'(.*)(?\'\'|$)%sU', '<em>$1</em>', $output);

		// Subscript
		$output = preg_replace('%,,(.*)(?:,,|$)%sU', '<sub>$1</sub>', $output);

		// Superscript
		$output = preg_replace('%\^\^(.*)(?:\^\^|$)%sU', '<sup>$1</sup>', $output);

		return $output;
	}// /method



	/**
	 * Parse all images in a string.
	 *
	 * @param  string  $string  The string to parse.
	 *
	 * @return  string  The string produced.
	 */
	private function _parseInlineImages($string) {
		// Parse the wiki-code images - {{xxxx}}
		$count = preg_match_all('%{{((.*))}}%U', $string, $matches);
		if ($count>0) {

			for($i=0; $i<$count; $i++) {
				$raw_image = $matches[1][$i];
				$bits = explode('|', $raw_image, 2);

				// If there's text/something to use for the link, use it
				if (array_key_exists(1, $bits)) {
					$link_string = "<img src=\"{$bits[0]}\" alt=\"{$bits[1]}\" />";
				} else {
					$link_string = "<img src=\"{$bits[0]}\" />";
				}

				$string = str_replace($matches[0][$i], $link_string, $string);
			}
		}

		return $string;
	}// /method



	/**
	 * Parse all links in a string.
	 *
	 * @param  string  $string  The string to parse.
	 *
	 * @return  string  The string produced.
	 */
	private function _parseInlineLinks($string) {
		// Parse plain links - http://xxxx
		$string = preg_replace('%(?<!\[)((http:|https:|ftp:)//(.*))(?=\s)%U','<a href="$1">$1</a>', $string);

		// Parse the wiki-code links - [[xxxx]]
		$count = preg_match_all('%\[\[((.*))]]%U', $string, $matches);
		if ($count>0) {

			for($i=0; $i<$count; $i++) {
				$raw_link = $matches[1][$i];
				$bits = explode('|', $raw_link, 2);

				// If there's text/something to use for the link, use it
				if (array_key_exists(1, $bits)) {
					$link_string = "<a href=\"{$bits[0]}\">{$bits[1]}</a>";
				} else {
					$link_string = "<a href=\"{$bits[0]}\">{$bits[0]}</a>";
				}

				$string = str_replace($matches[0][$i], $link_string, $string);
			}
		}

		return $string;
	}// /method



	/**
	 * Parse a bulleted or numbered list.
	 *
	 * @param  integer  $line_idx  The index of the line the list starts on.
	 *
	 * @return  mixed  The string produced.  On fail, null.
	 */
	private function _parseList(&$line_idx) {
		$output = null;

		$valid_chars = array ('*', '#');

		$lines = array();
		$curr_line = 0;

		$end = false;
		while (!$end) {
			$line = $this->_getLine($line_idx);
			if (empty($line)) {
				$end = true;
			} else {
				$first_char = substr($line, 0, 1);

				// If first char isn't a list-character, this line must follow the previous line
				if (!in_array($first_char, $valid_chars)) {
					$lines[$curr_line] .= ' ' . $line;
				} else {
					// It's a new line
					$curr_line++;
					$lines[$curr_line] = $line;
				}
				$line_idx++;
				$end = ($line_idx == $this->_lines_count-1);
			}
		}

		$stack = array();
		$curr_list = '';

		foreach($lines as $i => $line) {
			preg_match('%^([\*#]*)(.*)%', $line, $matches);
			if ($matches) {
				$list_tag = trim($matches[1]);
				$list_text = trim($matches[2]);

				$list_tag_len = strlen($list_tag);

				$last_tag_char = substr($list_tag, 0, -1);
				$html_list_type = ($last_tag_char=='#') ? 'ol' : 'ul' ;

				// If the line continues the current list
				if ($list_tag==$curr_list) {
					if ($curr_list!='') {
						$output .= "</li>\n";
					}
					$output .= str_pad("\t", $list_tag_len) . '<li>'. $this->_parseInline($list_text);
				} else {
					// If the new list is bigger than this list
					if ($list_tag_len>strlen($curr_list)) {
						$output .= "\n" . str_repeat("\t", $list_tag_len-1) . "<{$html_list_type}>\n";
						$output .= str_repeat("\t", $list_tag_len) . '<li>'. $this->_parseInline($list_text);
						array_push($stack, str_repeat("\t", $list_tag_len-1)."</{$html_list_type}>");
					} else {
						$level_diff = strlen($curr_list) - $list_tag_len;
						while ((!empty($stack)) && ($level_diff>0) ) {
							$list_close_tag = array_pop($stack);
							$output .= "</li>\n". $list_close_tag;
							$level_diff--;
						}
					}
					$curr_list = $list_tag;
				}
			}
		}

		while (!empty($stack)) {
			$list_close_tag = array_pop($stack);
			$output .= "</li>\n". $list_close_tag;
		}
		$output .= "\n";

		$line_idx--;
		return $output;
	}// /method



	/**
	 * Parse a paragraph.
	 *
	 * @param  integer  $line_idx  The index of the line the paragraph starts on.
	 *
	 * @return  mixed  The string produced.  On fail, null.
	 */
	private function _parseParagraph(&$line_idx) {
		$output = null;

		$invalid_chars = array ('*', '#', '-', '=', '|');

		$end = false;
		while (!$end) {
			$line = $this->_getLine($line_idx);

			if (empty($line)) {
				$end = true;
			} else {
				$first_char = substr($line, 0, 1);
				if (in_array($first_char, $invalid_chars)) {
					$end = true;
				} else {
					$output .= (empty($output)) ? $line: ' '.$line ;
					$end = ($line_idx == $this->_lines_count-1);
					$line_idx++;
				}
			}
		}

		$output = $this->_parseInline($output);
		$output = "<p>$output</p>\n\n";

		$line_idx--;   // Put the pointer back to the last line of the paragraph
		return $output;
	}// /method



	/**
	 * Parse a table.
	 *
	 * @param  integer  $line_idx  The index of the line the table starts on.
	 *
	 * @return  mixed  The string produced.  On fail, null.
	 */
	private function _parseTable(&$line_idx) {
		$output = null;

		$output .= "<table>\n";

		$end = false;
		while (!$end) {
			$line = $this->_getLine($line_idx);
			$temp = '';

			if (empty($line)) {
				$end = true;
			} else {
				$first_char = substr($line, 0, 1);
				if ($first_char!='|') {
					$end = true;
				} else {
					$output .= '<tr>';

					// Remove the | from the start/end
					$line = substr($line, 1);
					$len = strlen($line);
					if ($line[$len-1]=='|') {
						$line = substr($line, 0, -1);
					}

					// Get the cells
					$cells = explode('|', $line);
					$cell_count = count($cells);

					foreach($cells as $i => $cell) {

						if (empty($cell)) {
							$output .= '<td></td>';
						} else {
							// If the first char is an '=' it's a <th> tag
							if ($cell[0]=='=') {
								$temp = substr($cell, 1);

								// remove the optional trailing '='
								$len = strlen($temp);
								if ($len>1) {
									if ($temp[$len-1]=='=') {
										$temp = substr($temp, 0, -1);
									}
								}
								$output .= '<th>'. $this->_parseInline($temp) .'</th>';
							} else {
								$output .= '<td>'. $this->_parseInline($cell) .'</td>';
							}
						}
					}

					$output .= "</tr>\n";

					$end = ($line_idx == $this->_lines_count-1);
					$line_idx++;
				}
			}
		}

		$output .= "</table>\n\n";

		$line_idx--;
		return $output;
	}// /method



	/**
	 * Unescape any special sections/chars in the wiki string
	 *
	 * @param  string  $string  The string to parse.
	 *
	 * @return  mixed  The string produced.  On fail, null.
	 */
	private function _unescapeWikiString($string) {
		if ($this->_escaped_sections) {
			foreach($this->_escaped_sections as $i => $escaped_string) {
				$escape_block = "??ESC-{$this->_escape_id}-{$i}??";
				$string = str_replace($escape_block, $escaped_string, $string);
			}
		}
		return $string;
	}// /method



}// /class
?>