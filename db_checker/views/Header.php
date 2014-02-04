<?

namespace db_checker\views;

class Header
{
    
    public
    
    $box1, $box2, $info, $box3;
    
    public function makeHead()
    {
        if ( $this->box1 ) $this->box1 = "<div class='box1'>".$this->box1."</div>";
        if ( $this->box2 ) $this->box2 = "<div class='box2'>".$this->box2."</div>";
        if ( $this->info ) $this->info = "<span class='info'><img src='./ico/information.png' alt='info' />
        <span class='tip'>".$this->info."</span></span>";
        if ( $this->box3 ) $this->box3 .= "<div class='cleaner'></div>";
        
        return "<header>
            <div id='navLeft'>
            ".$this->box1 . $this->box2."
            </div>
            <nav>
                <ul>
                <li><a href='./index.php?str=porovnani'>Porovnání databází</a></li>
                <li><a href='./index.php?str=zobrazeni'>Information schema</a></li>
                </ul>
                ".$this->info."
            </nav>
            <div class='cleaner'></div>
            ".$this->box3."
        </header>
        <div id='content'>";
    }
}

?>