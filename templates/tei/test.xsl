<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xs="http://www.w3.org/2001/XMLSchema" exclude-result-prefixes="xs"
    xmlns:xd="http://www.oxygenxml.com/ns/doc/xsl" xmlns:html="http://www.w3.org/1999/xhtml"
    xmlns:anth="http://www.anthologize.org/ns" 
    xmlns:tei="http://www.tei-c.org/ns/1.0"    
    version="1.0">
    <xd:doc scope="stylesheet">
        <xd:desc>
            <xd:p><xd:b>Created on:</xd:b> Jul 28, 2010</xd:p>
            <xd:p><xd:b>Author:</xd:b> patrickmj</xd:p>
            <xd:p></xd:p>
        </xd:desc>
    </xd:doc>

    <xsl:template match="/">
        <xsl:apply-templates select="//tei:author/@ref"></xsl:apply-templates>
    </xsl:template>
    
<xsl:template match="tei:author/@ref">
    <xsl:variable name="userId"  select="." ></xsl:variable>    
    <xsl:value-of select="//tei:person[@xml:id = $userId]/tei:persName/tei:forename"/>
</xsl:template>    
    
<!--    


    <xsl:template match="tei:TEI">
        <xsl:apply-templates></xsl:apply-templates>
    </xsl:template>

    <xsl:template  match="//html:body">
        <xsl:value-of select="."></xsl:value-of>
    </xsl:template>-->
    
    <xsl:template match="*">
      <!--  <xsl:apply-templates></xsl:apply-templates>-->
    </xsl:template>

</xsl:stylesheet>
