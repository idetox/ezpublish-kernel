<?php
/**
 * File contains: ezp\Persistence\Tests\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\Group,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct;

/**
 * Test case for SectionHandler using in memory storage.
 *
 */
class ContentTypeHandlerTest extends HandlerTest
{
    /**
     * Test create group function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::createGroup
     */
    public function testCreateGroup()
    {
        $struct = new GroupCreateStruct();
        $struct->created = $struct->modified = time();
        $struct->creatorId = $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Media' );
        $struct->description = array( 'eng-GB' => 'Group for media content types' );
        $struct->identifier = 'media';
        $group = $this->repositoryHandler->ContentTypeHandler()->createGroup( $struct );
        $this->assertTrue( $group instanceof Group );
        $this->assertEquals( 2, $group->id );
        $this->assertEquals( array( 'eng-GB' => 'Media' ), $group->name );
    }

    /**
     * Test update group function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::updateGroup
     */
    public function testUpdateGroup()
    {
        $struct = new GroupUpdateStruct();
        $struct->id = 1;
        $struct->modified = time();
        $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Content2' );
        $struct->description = array( 'eng-GB' => 'TestTest' );
        $struct->identifier = 'content2';
        $this->repositoryHandler->ContentTypeHandler()->updateGroup( $struct );
        $group = $this->repositoryHandler->ContentTypeHandler()->loadGroup( 1 );
        $this->assertEquals( 1, $group->id );
        $this->assertEquals( array( 'eng-GB' => 'Content2' ), $group->name );
    }

    /**
     * Test delete group function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::deleteGroup
     */
    public function testDeleteGroup()
    {
        $this->repositoryHandler->ContentTypeHandler()->deleteGroup( 1 );
        $this->assertNull( $this->repositoryHandler->ContentTypeHandler()->loadGroup( 1 ) );
        $type = $this->repositoryHandler->ContentTypeHandler()->load( 1 );
        $this->assertEquals( array(), $type->contentTypeGroupIds );
    }

    /**
     * Test load group function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::loadGroup
     */
    public function testLoadGroup()
    {
        $obj = $this->repositoryHandler->ContentTypeHandler()->loadGroup( 1 );
        $this->assertTrue( $obj instanceof Group );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( array( 'eng-GB' => 'Content' ), $obj->name );
    }

    /**
     * Test load all groups function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $list = $this->repositoryHandler->ContentTypeHandler()->loadAllGroups();
        $this->assertEquals( 1, count( $list ) );
        $this->assertTrue( $list[0] instanceof Group );
        $this->assertEquals( 1, $list[0]->id );
        $this->assertEquals( array( 'eng-GB' => 'Content' ), $list[0]->name );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::loadContentTypes
     */
    public function testLoadByGroup()
    {
        $list = $this->repositoryHandler->ContentTypeHandler()->loadContentTypes( 1, 0 );
        $this->assertEquals( 1, count( $list ) );
        $this->assertTrue( $list[0] instanceof Type );
        $this->assertEquals( 1, $list[0]->id );
        $this->assertEquals( 'folder', $list[0]->identifier );

        $list = $this->repositoryHandler->ContentTypeHandler()->loadContentTypes( 2, 0 );
        $this->assertEquals( array(), $list );
    }

    /**
     * Test load function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::load
     */
    public function testLoad()
    {
        $obj = $this->repositoryHandler->ContentTypeHandler()->load( 1, 0 );
        $this->assertTrue( $obj instanceof Type );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'folder', $obj->identifier );
        $this->assertEquals( 1, count( $obj->fieldDefinitions ) );
        $this->assertEquals( 'Name', $obj->fieldDefinitions[0]->name['eng-GB'] );

        $obj = $this->repositoryHandler->ContentTypeHandler()->load( 2, 0 );
        $this->assertNull( $obj );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::create
     */
    public function testCreate()
    {
        $handler = $this->repositoryHandler->ContentTypeHandler();
        $obj = $handler->create( $this->getTypeCreateStruct() );
        $this->assertTrue( $obj instanceof Type );
        $this->assertEquals( 2, $obj->id );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $this->assertEquals( array(), $obj->fieldDefinitions );
    }

    /**
     * Test create function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::create
     */
    public function testCreateWithFieldDefinition()
    {
        $handler = $this->repositoryHandler->ContentTypeHandler();
        $struct = $this->getTypeCreateStruct();
        $struct->fieldDefinitions[] = $field =$this->getTypeFieldDefinition();

        $obj = $handler->create( $struct );
        $this->assertTrue( $obj instanceof Type );
        $this->assertEquals( 2, $obj->id );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $field->id = $obj->fieldDefinitions[0]->id;
        $this->assertEquals( array( $field ), $obj->fieldDefinitions );
    }

    /**
     * Test update function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::update
     */
    public function testUpdate()
    {
        $handler = $this->repositoryHandler->ContentTypeHandler();
        $handler->update( 1, 0, $this->getTypeUpdateStruct() );
        $obj = $handler->load( 1, 0 );
        $this->assertTrue( $obj instanceof Type );
        $this->assertEquals( 1, $obj->id );
        $this->assertEquals( 'article', $obj->identifier );
        $this->assertEquals( "<short_title|title>", $obj->nameSchema );
        $this->assertEquals( 10, $obj->modifierId );
    }

    /**
     * Test delete function
     *
     * @covers ezp\Persistence\Tests\InMemoryEngine\ContentTypeHandler::delete
     */
    public function testDelete()
    {
        $handler = $this->repositoryHandler->ContentTypeHandler();

        $handler->delete( 1, 0 );
        $this->assertNull( $handler->load( 1, 0 ) );
    }

    /**
     * @return \ezp\Persistence\Content\Type\CreateStruct
     */
    private function getTypeCreateStruct()
    {
        $struct = new CreateStruct();
        $struct->created = $struct->modified = time();
        $struct->creatorId = $struct->modifierId = 14;
        $struct->name = array( 'eng-GB' => 'Article' );
        $struct->description = array( 'eng-GB' => 'Article content type' );
        $struct->identifier = 'article';
        $struct->isContainer = true;
        $struct->version = 0;
        $struct->initialLanguageId = 2;
        $struct->nameSchema = "<short_title|title>";
        $struct->fieldDefinitions = array();
        $struct->contentTypeGroupIds = array( 1 );
        return $struct;
    }

    /**
     * @return \ezp\Persistence\Content\Type\UpdateStruct
     */
    protected function getTypeUpdateStruct()
    {
        $struct = new UpdateStruct();
        $struct->modified = time();
        $struct->modifierId = 10;
        $struct->name = array( 'eng-GB' => 'Article' );
        $struct->description = array( 'eng-GB' => 'Article content type' );
        $struct->identifier = 'article';
        $struct->isContainer = true;
        $struct->initialLanguageId = 2;
        $struct->nameSchema = "<short_title|title>";
        return $struct;
    }

    /**
     * @return \ezp\Persistence\Content\Type\FieldDefinition
     */
    protected function getTypeFieldDefinition()
    {
        $field =  new FieldDefinition();
        $field->identifier = 'title';
        $field->fieldType = 'ezstring';
        $field->position = 0;
        $field->isTranslatable = $field->isRequired = true;
        $field->isInfoCollector = false;
        $field->defaultValue = 'New Article';
        $field->name = array( 'eng-GB' => "Title" );
        $field->description = array( 'eng-GB' => "Title, used for headers, and url if short_title is empty" );
        return $field;
    }
}
