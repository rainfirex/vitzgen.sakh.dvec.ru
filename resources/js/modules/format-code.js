export const formatCode = (code) => {
    // - границ слов /\b(var)/
    const key_words  = /\b(var|function|typeof|new|return|for|in|while|break|do|continue|switch|case|and|end|class|or)([^a-z0-9\$_])/gi;
    const functions  = /\b(LENGTH|TO_CHAR|SUM|NVL|wm_concat|substr|DECODE|max|replace|min|CONCAT)/gi;
    const conditions = /\b(if|else|elseif|as)/gi;
    const sql_words  = /\b(from|table|select|where|distinct|is|not|null|then|when|group|by|inner join|on|order)/gi;
    const comment    = /('.*?')/gi;
    const commentDouble  = /(\\".*?\\")/gi;
    const field = /\b(.[a-z]+)/gi; // не работает

    return code
        .replace(key_words, '<span style="color: #005cbf"><b>$1</b></span>$2')
        .replace(functions,'<span style="color: #bf2ea9"><b>$1</b></span>')
        .replace(conditions, '<span style="color: red"><b>$1</b></span>')
        .replace(sql_words, '<span style="color: #1534c9"><b>$1</b></span>')
        .replace(comment, '<span style="color: #2b8e19"><i>$1</i></span>')
        .replace(commentDouble, '<span style="color: #2b8e19"><i>$1</i></span>')
        // .replace(field, '<span style="color: #1e888e"><i>$1</i></span>')
        .replace(/\n/g,'\n  ')
        .replace(/\t/g,'    ');

        // .replace(/(\/\/[^\n\r]*(\n|\r\n))/g,'<span style="color: #c9c60a">$1</span>')
        // .replace(/(\{|\}|\]|\[|\|\(|\))/g,'<span style="color: #062c33">$1</span>')
        // .replace(/(\/\/[^\n\r]*(\n|\r\n))/g,'<span style="color: #1c7430">$1</span>');
};
