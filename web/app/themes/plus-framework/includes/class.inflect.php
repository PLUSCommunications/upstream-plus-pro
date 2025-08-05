<?php
/**
 * Inflection for Singular / Plural words
 * @author http://kuwamoto.org/2007/12/17/improved-pluralizing-in-php-actionscript-and-ror/
 * @license The MIT License
 * @see https://gist.githubusercontent.com/tbrianjones/ba0460cc1d55f357e00b/raw/a109e4e32c6a913aea16185a06b84c3381de275f/Inflect.php
*/ 

if ( !class_exists('inflect') ) :
  class inflect {
    static $plural = array(
      '/(quiz)$/i'                     => "$1zes",
      '/^(ox)$/i'                      => "$1en",
      '/([m|l])ouse$/i'                => "$1ice",
      '/(matr|vert|ind)ix|ex$/i'       => "$1ices",
      '/(x|ch|ss|sh)$/i'               => "$1es",
      '/([^aeiouy]|qu)y$/i'            => "$1ies",
      '/(hive)$/i'                     => "$1s",
      '/(?:([^f])fe|([lr])f)$/i'       => "$1$2ves",
      '/(shea|lea|loa|thie)f$/i'       => "$1ves",
      '/sis$/i'                        => "ses",
      '/([ti])um$/i'                   => "$1a",
      '/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
      '/(bu)s$/i'                      => "$1ses",
      '/(alias)$/i'                    => "$1es",
      '/(octop)us$/i'                  => "$1i",
      '/(ax|test)is$/i'                => "$1es",
      '/(us)$/i'                       => "$1es",
      '/s$/i'                          => "s",
      '/$/'                            => "s"
    );
    
    static $singular = array(
      '/(quiz)zes$/i'                                                    => "$1",
      '/(matr)ices$/i'                                                   => "$1ix",
      '/(vert|ind)ices$/i'                                               => "$1ex",
      '/^(ox)en$/i'                                                      => "$1",
      '/(alias)es$/i'                                                    => "$1",
      '/(octop|vir)i$/i'                                                 => "$1us",
      '/(cris|ax|test)es$/i'                                             => "$1is",
      '/(shoe)s$/i'                                                      => "$1",
      '/(o)es$/i'                                                        => "$1",
      '/(bus)es$/i'                                                      => "$1",
      '/([m|l])ice$/i'                                                   => "$1ouse",
      '/(x|ch|ss|sh)es$/i'                                               => "$1",
      '/(m)ovies$/i'                                                     => "$1ovie",
      '/(s)eries$/i'                                                     => "$1eries",
      '/([^aeiouy]|qu)ies$/i'                                            => "$1y",
      '/([lr])ves$/i'                                                    => "$1f",
      '/(tive)s$/i'                                                      => "$1",
      '/(hive)s$/i'                                                      => "$1",
      '/(li|wi|kni)ves$/i'                                               => "$1fe",
      '/(shea|loa|lea|thie)ves$/i'                                       => "$1f",
      '/(^analy)ses$/i'                                                  => "$1sis",
      '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => "$1$2sis",
      '/([ti])a$/i'                                                      => "$1um",
      '/(n)ews$/i'                                                       => "$1ews",
      '/(h|bl)ouses$/i'                                                  => "$1ouse",
      '/(corpse)s$/i'                                                    => "$1",
      '/(us)es$/i'                                                       => "$1",
      '/s$/i'                                                            => ""
    );
    
    static $irregular = array(
      'abacus'       => 'abaci',
      'alumnus'      => 'alumni',
      'automaton'    => 'automata',
      'cactus'       => 'cacti',
      'child'        => 'children',
      'criterion'    => 'criteria',
      'die'          => 'dice',
      'foot'         => 'feet',
      'fungus'       => 'fungi',
      'goose'        => 'geese',
      'hippopotamus' => 'hippopotami',
      'man'          => 'men',
      'memorandum'   => 'memoranda',
      'move'         => 'moves',
      'nucleus'      => 'nuclei',
      'person'       => 'people',
      'phenomenon'   => 'phenomena',
      'sex'          => 'sexes',
      'tooth'        => 'teeth',
      'valve'        => 'valves',
    );
    
    static $uncountable = array(
      'aggression', 'air', 'accommodation', 'art', 'anger', 'art', 'advice', 'adulthood', 'applause', 'advertising', 'athletics', 'advice', 'alcohol', 'aid', 'assistance', 'access', 'air', 'aid', 'anger', 'accommodation', 'arithmetic', 'assistance', 'advertising',
      'bread', 'business', 'butter', 'bacon', 'baggage', 'ballet', 'beauty', 'beef', 'beer', 'biology', 'blood', 'botany',
      'calm', 'cash', 'chaos', 'cheese', 'childhood', 'clothing', 'coffee', 'content', 'corruption', 'courage', 'currency', 'carbon', 'cardboard', 'chalk', 'chess', 'coal', 'commerce', 'compassion', 'comprehension', 'cotton',
      'damage', 'dancing', 'danger', 'data', 'delight', 'dessert', 'dignity', 'dirt', 'distribution', 'dust', 'darkness', 'deer', 'determination',
      'economics', 'education', 'electricity', 'employment', 'energy', 'entertainment', 'enthusiasm', 'equipment', 'evidence', 'engineering', 'enjoyment', 'envy', 'ethics', 'evolution',
      'failure', 'fame', 'fire', 'fish', 'flour', 'food', 'freedom', 'friendship', 'fuel', 'fun', 'furniture', 'faith', 'fiction', 'flu', 'fruit',
      'genetics', 'gold', 'grammar', 'guilt', 'garbage', 'garlic', 'gas', 'glass', 'golf', 'gossip', 'grass', 'gratitude', 'grief', 'ground', 'gymnastics',
      'hair', 'happiness', 'hardware', 'harm', 'hate', 'hatred', 'health', 'heat', 'height', 'help', 'homework', 'honesty', 'honey', 'hospitality', 'housework', 'humour', 'hunger', 'hydrogen',
      'imagination', 'importance', 'information', 'innocence', 'intelligence', 'ice', 'ice cream', 'inflation', 'injustice', 'iron', 'irony',
      'jam', 'jealousy', 'jelly', 'joy', 'judo', 'juice', 'justice',
      'karate', 'kindness', 'knowledge',
      'labour', 'lack', 'laughter', 'legislation', 'leisure', 'literature', 'litter', 'logic', 'love', 'luck', 'land', 'lava', 'leather', 'lightning', 'linguistics', 'livestock', 'loneliness', 'luggage',
      'magic', 'management', 'metal', 'milk', 'money', 'motherhood', 'motivation', 'music', 'machinery', 'mail', 'mankind', 'marble', 'mathematics', 'mayonnaise', 'measles', 'meat', 'methane', 'mud',
      'nature', 'news', 'nutrition', 'nitrogen', 'nonsense', 'nurture',
      'obesity', 'oil', 'old age', 'oxygen', 'obedience',
      'paper', 'patience', 'permission', 'pollution', 'poverty', 'power', 'pride', 'production', 'progress', 'pronunciation', 'publicity', 'punctuation', 'passion', 'pasta', 'physics', 'poetry', 'psychology',
      'quality', 'quantity', 'quartz',
      'racism', 'rain', 'relaxation', 'research', 'respect', 'rice', 'room', 'rubbish', 'recreation', 'reliability', 'revenge', 'rum',
      'safety', 'salt', 'sand', 'seafood', 'series', 'sheep', 'shopping', 'silence', 'sleep', 'smoke', 'snow', 'software', 'soup', 'space', 'species', 'speed', 'spelling', 'sport', 'strength', 'stress', 'success', 'sugar', 'sunshine', 'salad', 'satire', 'scenery', 'seaside', 'shame', 'smoking', 'soap', 'soil', 'sorrow', 'steam', 'stuff', 'stupidity', 'symmetry',
      'trust', 'tennis', 'travel', 'thunder', 'time', 'traffic', 'transportation', 'tolerance', 'trade', 'thirst', 'tea', 'toast', 'timber',
      'understanding', 'underwear', 'unemployment', 'unity', 'usage',
      'validity', 'veal', 'vegetation', 'vegetarianism', 'vengeance', 'violence', 'vision', 'vitality',
      'warmth', 'water', 'wealth', 'weather', 'weight', 'welfare', 'wheat', 'width', 'wildlife', 'wisdom', 'wood', 'work', 'whiskey', 'wine', 'wool',
      'yeast', 'yoga', 'youth',
      'zinc', 'zoology',
    );
    
    public static function pluralize( $string ) {
      return inflect::process(self::$plural, $string);
    }
    
    public static function singularize( $string ) {
      return inflect::process(self::$singular, $string);
    }
    
    private static function process( $patterns, $string ) {
      /* Save some time in the case that singular and plural are the same */
        if ( in_array( strtolower( $string ), self::$uncountable ) ) return $string;
        
      /* Check for irregular plural forms */
        foreach ( self::$irregular as $result => $pattern ) {
          $pattern = '/' . $pattern . '$/i';
          
          if ( preg_match( $pattern, $string ) )
            return preg_replace( $pattern, $result, $string);
        }
        
      /* Check for matches using regular expressions */
        foreach ( $patterns as $pattern => $result ) {
          if ( preg_match( $pattern, $string ) )
            return preg_replace( $pattern, $result, $string );
        }
    }
  }
endif;