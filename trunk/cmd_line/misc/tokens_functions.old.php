   public function replace_with_hashes() {

       if ($this->opt->get('dry_run')) {

           return;

       }

        preg_match_all('/<\?(?:php)?.*\?>/isUx', $this->buffer, $matches, PREG_PATTERN_ORDER);
        $this->out(null);

        foreach($matches[0] as $key => $php) {

            if ($key % 100 === 0) {
                $this->out('Replacing PHP block... Step ' . ($key / 100));
            }

            $new_php = '';
            $tokens = token_get_all($php);

            $alias =& $tokens;

            foreach($alias as $token) {

                if (is_scalar($token)) {

                    // this will most likely be ";"
                    $new_php .= $token;

                } else {

                    list($id, $text) = $token;

                    switch($id) {

                        /*

                    case T_COMMENT:
                    case T_DOC_COMMENT:

                        if ($this->cfg->get('StripWhiteSpace') && !$this->opt->get('strip_comments')) {

                            $new_php .= $text;

                        }

                        break;

                        */

#                    case T_BAD_CHARACTER:
 #                       break;

                        /*

                    case T_STRING:
                        #$new_php .= 'F' . substr(md5($text), 1, $this->cfg->get('MD5KeyLen'));
                        echo '--' . $text . "\n";
                        continue;
                        break;

                        */

                    case T_VARIABLE:

                        $this->var_list[] = str_replace('$', '', $text);
                        $new_php .= $text;
                        break;
                        
                    case T_END_HEREDOC:
                     
                        $new_php .= $text . "\n";
                        break;

                    case T_FUNCTION:
                        @list(,$t_text) = next(next(next(next($tokens))));
#                        $this->func_list[] = $t_text;
#                        echo '--' . $t_text . "\n";
#                        prev($tokens);
                        $new_php .= $text . ' F' . substr(md5($t_text), 1, $this->cfg->get('MD5KeyLen'));
                        break;


#                        $this->buffer = str_replace($var, 'F' . substr(md5($var), 1, $this->cfg->get('MD5KeyLen')), $this->buffer);


                    default:

                        $new_php .= $text;
                        break;

                    }

                }

            }
            
            $this->buffer = str_replace($php, $new_php, $this->buffer);

        }

        $this->replace_variables();
#        $this->replace_functions();

    }

    private function replace_functions() {

        $ar = array_diff($this->func_list, $this->cfg->get('UdExcFuncArray'));
        $ar = array_unique($ar);

        foreach ($ar as $var) {

            $this->buffer = str_replace($var, 'F' . substr(md5($var), 1, $this->cfg->get('MD5KeyLen')), $this->buffer);

        }

    }

    private function replace_variables() {

        $ar = array_diff($this->var_list, array_merge($this->cfg->get('StdExcVarArray'), $this->cfg->get('UdExcVarArray')));
        $ar = array_unique($ar);

        foreach ($ar as $var) {

            $this->buffer = str_replace('$' . $var, '$V' . substr(md5($var), 1, $this->cfg->get('MD5KeyLen')), $this->buffer);

        }

    }

