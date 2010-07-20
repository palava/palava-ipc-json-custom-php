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

import de.cosmocode.palava.ipc.IpcCommand;
import de.cosmocode.rendering.Renderer;
import de.cosmocode.rendering.RenderingException;
import de.cosmocode.rendering.ValueRenderer;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.annotation.Nonnull;
import javax.annotation.Nullable;
import java.lang.annotation.Annotation;

/**
 * @author Tobias Sarnowski
 */
final class CommandRenderer implements ValueRenderer<Class<? extends IpcCommand>> {
    private static final Logger LOG = LoggerFactory.getLogger(CommandRenderer.class);


    @Override
    public void render(@Nullable Class<? extends IpcCommand> c, @Nonnull Renderer r) throws RenderingException {
        if (c == null) {
            LOG.debug("Skipping rendering; command is null");
            return;
        }
        
        // the class informations
        r.map();

        // class
        r.key("class").value(c.getName());


        // description
        IpcCommand.Description description = c.getAnnotation(IpcCommand.Description.class);
        if (description != null) {
            r.key("description").value(description.value());
        }


        // params
        IpcCommand.Param[] paramList = new IpcCommand.Param[0];

        IpcCommand.Params params = c.getAnnotation(IpcCommand.Params.class);
        if (params != null) {
            paramList = params.value();
        } else {
            IpcCommand.Param param = c.getAnnotation(IpcCommand.Param.class);
            if (param != null) {
                paramList = new IpcCommand.Param[]{param};
            }
        }

        r.key("params").list();
        for (IpcCommand.Param param: paramList) {
            r.map();
            r.key("name").value(param.name());
            r.key("description").value(param.description());
            r.key("type").value(param.type());
            r.key("optional").value(param.optional());
            r.key("defaultValue").value(param.defaultValue());
            r.endMap();
        }
        r.endList();


        // returns
        IpcCommand.Return[] returnList = new IpcCommand.Return[0];

        IpcCommand.Returns returns = c.getAnnotation(IpcCommand.Returns.class);
        if (returns != null) {
            returnList = returns.value();
        } else {
            IpcCommand.Return ret = c.getAnnotation(IpcCommand.Return.class);
            if (ret != null) {
                returnList = new IpcCommand.Return[]{ret};
            }
        }

        r.key("returns").list();
        for (IpcCommand.Return ret: returnList) {
            r.map();
            r.key("name").value(ret.name());
            r.key("description").value(ret.description());
            r.endMap();
        }
        r.endList();


        // throws
        IpcCommand.Throw[] throwsList = new IpcCommand.Throw[0];

        IpcCommand.Throws throwss = c.getAnnotation(IpcCommand.Throws.class);
        if (throwss != null) {
            throwsList = throwss.value();
        } else {
            IpcCommand.Throw thro = c.getAnnotation(IpcCommand.Throw.class);
            if (thro != null) {
                throwsList = new IpcCommand.Throw[]{thro};
            }
        }

        r.key("throws").list();
        for (IpcCommand.Throw thro: throwsList) {
            r.map();
            r.key("name").value(thro.name());
            r.key("description").value(thro.description());
            r.endMap();
        }
        r.endList();


        // every other annotation on the class
        r.key("annotations").list();
        for (Annotation a: c.getAnnotations()) {
            if (a.annotationType().getAnnotation(IpcCommand.Meta.class) != null) {
                // ipc command meta informations are already parsed
                continue;
            }

            r.map();
            r.key("name").value(a.annotationType().getName());
            r.endMap();
        }
        r.endList();


        // the class informations
        r.endMap();
    }
}