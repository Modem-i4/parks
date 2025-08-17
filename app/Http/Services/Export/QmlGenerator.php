<?php

namespace App\Http\Services\Export;

class QmlGenerator
{
    public static function generate(
        string $colorField = 'm_color',
        string $symbolField = 'm_symbol',
        string $sizeField = 'm_size',
        array $colors = ['#000000','#66a9ff','#9d9fa3','#fcd45b','green','red']
    ): string {
        $c = htmlspecialchars($colorField, ENT_QUOTES);
        $s = htmlspecialchars($symbolField, ENT_QUOTES);
        $z = htmlspecialchars($sizeField, ENT_QUOTES);

        $exprName = "CASE
WHEN lower(\"{$s}\")='triangle'  THEN 'triangle'
WHEN lower(\"{$s}\")='rectangle' THEN 'square'
WHEN lower(\"{$s}\")='diamond'   THEN 'diamond'
ELSE 'circle'
END";

        $exprSize = "CASE
WHEN lower(\"{$z}\")='large'  THEN 6
WHEN lower(\"{$z}\")='medium' THEN 4
ELSE 3
END";

        $catXml = [];
        $symXml = [];
        $i = 0;
        foreach ($colors as $col) {
            $val = htmlspecialchars($col, ENT_QUOTES);
            $uuid = sprintf('{%s}', bin2hex(random_bytes(16)));
            $catXml[] = "      <category value=\"{$val}\" uuid=\"{$uuid}\" label=\"{$val}\" type=\"string\" symbol=\"{$i}\" render=\"true\"/>";

            $symXml[] = <<<XML
        <symbol clip_to_extent="1" force_rhr="0" alpha="1" name="{$i}" is_animated="0" type="marker" frame_rate="10">
            <layer pass="0" class="SimpleMarker" locked="0" enabled="1">
            <Option type="Map">
                <Option name="angle" value="0" type="QString"/>
                <Option name="name" value="circle" type="QString"/>
                <Option name="color" value="{$val}" type="QString"/>
                <Option name="outline_color" value="0,0,0,255" type="QString"/>
                <Option name="outline_width" value="0.5" type="QString"/>
                <Option name="scale_method" value="diameter" type="QString"/>
                <Option name="size" value="3" type="QString"/>
                <Option name="size_unit" value="MM" type="QString"/>
            </Option>
            <data_defined_properties>
                <Option type="Map">
                <Option name="name" value="" type="QString"/>
                <Option name="type" value="collection" type="QString"/>
                <Option name="properties" type="Map">
                    <Option name="name" type="Map">
                    <Option name="active" value="1" type="bool"/>
                    <Option name="type" value="3" type="int"/>
                    <Option name="expression" type="QString"><![CDATA[
                        {$exprName}
                    ]]></Option>
                    </Option>
                    <Option name="size" type="Map">
                    <Option name="active" value="1" type="bool"/>
                    <Option name="type" value="3" type="int"/>
                    <Option name="expression" type="QString"><![CDATA[
                        {$exprSize}
                    ]]></Option>
                    </Option>
                </Option>
                </Option>
            </data_defined_properties>
            </layer>
        </symbol>
    XML;
            $i++;
        }
        $catXml = implode("\n", $catXml);
        $symXml = implode("\n", $symXml);

        $renderer = <<<XML
    <renderer-v2 enableorderby="1" attr="coalesce(&quot;{$c}&quot;,'gray')" referencescale="-1" forceraster="0" type="categorizedSymbol" symbollevels="0">
        <categories>
    {$catXml}
        </categories>
        <symbols>
    {$symXml}
        </symbols>
        <source-symbol>
        <symbol clip_to_extent="1" force_rhr="0" alpha="1" name="0" is_animated="0" type="marker" frame_rate="10">
            <layer pass="0" class="SimpleMarker" locked="0" enabled="1">
            <Option type="Map">
                <Option name="name" value="circle" type="QString"/>
                <Option name="color" value="0,0,0,255" type="QString"/>
                <Option name="outline_color" value="0,0,0,255" type="QString"/>
                <Option name="outline_width" value="0.5" type="QString"/>
                <Option name="size" value="3" type="QString"/>
                <Option name="size_unit" value="MM" type="QString"/>
                <Option name="scale_method" value="diameter" type="QString"/>
            </Option>
            </layer>
        </symbol>
        </source-symbol>
        <rotation/>
        <sizescale/>
    </renderer-v2>
    XML;

        return <<<QML
<qgis styleCategories="Symbology" version="3.40.9">
{$renderer}
<layerGeometryType>0</layerGeometryType>
</qgis>
QML;
    }
}
