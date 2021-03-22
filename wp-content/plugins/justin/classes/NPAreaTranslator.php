<?php

namespace morkva\JustinShip\classes;

class NPAreaTranslator
{
  private $translates = [''];

  public function translateAreas($areas)
  {
    if (get_option('woo_justin_np_lang') === 'ru') {
      foreach ($areas as &$area) {
        if (isset($this->translates[ $area['ref'] ])) {
          $area['description'] = $this->translates[ $area['ref'] ];
        }
      }
    }

    return $areas;
  }
}
