/**
 * Copyright 2010 CosmoCode GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package de.cosmocode.palava.ipc.json.custom.php.explorer;

import java.lang.annotation.Annotation;

import javax.annotation.Nonnull;
import javax.annotation.Nullable;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import de.cosmocode.palava.ipc.IpcCommand;
import de.cosmocode.palava.ipc.IpcCommand.Description;
import de.cosmocode.palava.ipc.IpcCommand.Meta;
import de.cosmocode.palava.ipc.IpcCommand.Param;
import de.cosmocode.palava.ipc.IpcCommand.Params;
import de.cosmocode.palava.ipc.IpcCommand.Return;
import de.cosmocode.palava.ipc.IpcCommand.Returns;
import de.cosmocode.palava.ipc.IpcCommand.Throw;
import de.cosmocode.palava.ipc.IpcCommand.Throws;
import de.cosmocode.rendering.Renderer;
import de.cosmocode.rendering.RenderingException;
import de.cosmocode.rendering.ValueRenderer;

/**
 * {@link ValueRenderer} for {@link IpcCommand} classes.
 * 
 * @author Tobias Sarnowski
 */
enum CommandRenderer implements ValueRenderer<Class<? extends IpcCommand>> {
    
    INSTANCE;
    
    private static final Logger LOG = LoggerFactory.getLogger(CommandRenderer.class);
    
    @Override
    public void render(@Nullable Class<? extends IpcCommand> c, @Nonnull Renderer r) throws RenderingException {
        if (c == null) {
            LOG.debug("Command is null");
            r.nullValue();
        } else {
            r.map();
            
            basics(c, r);
            params(c, r);
            returns(c, r);
            throwss(c, r);
            annotations(c, r);
            
            r.endMap();
        }
    }
    
    private void basics(Class<? extends IpcCommand> c, Renderer r) {
        // class
        r.key("class").value(c.getName());

        // description
        final Description description = c.getAnnotation(Description.class);
        if (description != null) {
            r.key("description").value(description.value());
        }
    }

    private void params(Class<? extends IpcCommand> c, Renderer r) {
        final Param[] paramList;

        final Params params = c.getAnnotation(Params.class);
        if (params == null) {
            final Param param = c.getAnnotation(Param.class);
            if (param == null) {
                paramList = new Param[0];
            } else {
                paramList = new Param[]{param};
            }
        } else {
            paramList = params.value();
        }

        r.key("params").list();
        for (Param param : paramList) {
            r.map();
            r.key("name").value(param.name());
            r.key("description").value(param.description());
            r.key("type").value(param.type());
            r.key("optional").value(param.optional());
            r.key("defaultValue").value(param.defaultValue());
            r.endMap();
        }
        r.endList();
    }
    
    private void returns(Class<? extends IpcCommand> c, Renderer r) {
        final Return[] returnList;

        final Returns returns = c.getAnnotation(Returns.class);
        if (returns == null) {
            final Return ret = c.getAnnotation(Return.class);
            if (ret == null) {
                returnList = new Return[0];
            } else {
                returnList = new Return[]{ret};
            }
        } else {
            returnList = returns.value();
        }

        r.key("returns").list();
        for (Return ret : returnList) {
            r.map();
            r.key("name").value(ret.name());
            r.key("description").value(ret.description());
            r.endMap();
        }
        r.endList();
    }
    
    private void throwss(Class<? extends IpcCommand> c, Renderer r) {
        final Throw[] throwsList;

        final Throws throwss = c.getAnnotation(Throws.class);
        if (throwss == null) {
            final Throw thro = c.getAnnotation(Throw.class);
            if (thro == null) {
                throwsList = new Throw[0];
            } else {
                throwsList = new Throw[]{thro};
            }
        } else {
            throwsList = throwss.value();
        }

        r.key("throws").list();
        for (Throw thro : throwsList) {
            r.map();
            r.key("name").value(thro.name().getName());
            r.key("description").value(thro.description());
            r.endMap();
        }
        r.endList();
    }
    
    private void annotations(Class<? extends IpcCommand> c, Renderer r) {
        r.key("annotations").list();
        for (Annotation a : c.getAnnotations()) {
            if (a.annotationType().isAnnotationPresent(Meta.class)) {
                // ipc command meta informations are already parsed
                continue;
            }
            r.map();
            r.key("name").value(a.annotationType().getName());
            r.endMap();
        }
        r.endList();
    }
    
}
