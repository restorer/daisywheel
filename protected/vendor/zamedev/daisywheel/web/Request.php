<?php

namespace daisywheel\web;

use daisywheel\core\Component;

class Request extends Component
{
    // казалсь бы, htmlspecialchars(fromGet('xxx', '')) - это пуленепробиваемо.
    // как бы не так, ибо php может автоматически создать массив.
    // т.е. юзер правит запросе ?xxx=yyy на ?xxx[]=yyy и теперь htmlspecialchars упадёт с ошибкой.
}
