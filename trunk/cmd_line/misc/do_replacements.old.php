    public function do_replacements() {

        if (!$this->cfg->get('StripWhiteSpace') && $this->opt->get('strip_comments')) {
            $this->strip_comments();
        }

        /*
         * Match function definitions
         * PREG_PATTER_ORDER is unneeded
         * '("\\<\\(function\\)\\s-+&?\\(\\(?:\\sw\\|\\s_\\)+\\)\\s-*("
         */

        $this->out("\n" . 'Scanning for functions...');
        preg_match_all('/!(?:new\s+)function\s+(\w+)\s*\(/i', $this->buffer, $catches, PREG_PATTERN_ORDER);
        $this->out('Replacing functions with MD5 keys... Done');

        foreach($catches[1] as $catch) {

            if (!in_array($catch, $this->cfg->get('UdExcFuncArray'))) {

                $this->buffer = preg_replace('/' . $catch . '\s*\(/', 'F' . substr(md5($catch), 1, $this->cfg->get('MD5KeyLen')) . '(', $this->buffer);

            }

        }

        /*
         * Match variables
         * PREG_PATTER_ORDER is unneeded
         * '("\\$\\(\\(?:\\sw\\|\\s_\\)+\\)" (1 font-lock-variable-name-face)) ; $variable
         */

#/*
        $this->out("\n" . 'Scanning for variables...');
        preg_match_all('/\$(\w+)/i', $this->buffer, $catches, PREG_PATTERN_ORDER);
        $this->out('Replacing variables with MD5 keys... Done');

        $catches = (array) array_unique($catches[1]);

        foreach($catches as $catch) {

            if (!in_array($catch, array_merge($this->cfg->get('StdExcVarArray'), $this->cfg->get('UdExcVarArray')))) {

                $this->buffer = str_replace('$' . $catch, '$V' . substr(md5($catch), 1, $this->cfg->get('MD5KeyLen')), $this->buffer);

            }

        }

#*/

    }
