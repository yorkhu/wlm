<xsl:stylesheet version = '1.0'
	xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
<xsl:output method = "html" omit-xml-declaration = "yes"
	encoding="iso-8859-2" indent="yes" />

<xsl:template match="/source/js">
<script type="text/javascript" language="javascript"> 
<xsl:value-of select="//js" />
</script>

</xsl:template>
</xsl:stylesheet>
