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

import java.util.Map;

import com.google.common.base.Function;
import com.google.common.base.Functions;
import com.google.common.base.Predicate;
import com.google.common.collect.Iterables;
import com.google.inject.Inject;
import com.google.inject.Provider;
import com.google.inject.Singleton;

import de.cosmocode.collections.utility.UtilityList;
import de.cosmocode.commons.reflect.Classpath;
import de.cosmocode.commons.reflect.Packages;
import de.cosmocode.commons.reflect.Reflection;
import de.cosmocode.palava.ipc.IpcCall;
import de.cosmocode.palava.ipc.IpcCommand;
import de.cosmocode.palava.ipc.IpcCommand.Description;
import de.cosmocode.palava.ipc.IpcCommand.Param;
import de.cosmocode.palava.ipc.IpcCommand.Return;
import de.cosmocode.palava.ipc.IpcCommandExecutionException;
import de.cosmocode.palava.ipc.cache.Cached;
import de.cosmocode.rendering.Renderer;

/**
 * See below.
 * 
 * @author Tobias Sarnowski
 */
@Description("Returns a list of all commands available in the JVM.")
@Param(name = "packages", description = "a list of packages to search in")
@Return(name = "commands", description = "a list of all commands")
@Cached
@Singleton
public final class Commands implements IpcCommand {

    private static final Predicate<Class<?>> FILTER = 
            Reflection.isSubtypeOf(IpcCommand.class).and(
            Reflection.isInterface().negate()).and(
            Reflection.isAbstract().negate()
        );
    
    private final Provider<Renderer> rendererProvider;

    @Inject
    Commands(Provider<Renderer> rendererProvider) {
        this.rendererProvider = rendererProvider;
    }

    @Override
    public void execute(IpcCall call, Map<String, Object> result) throws IpcCommandExecutionException {
        final UtilityList<Object> packages = call.getArguments().getList("packages");

        final Renderer renderer = rendererProvider.get();

        final Classpath cp = Reflection.defaultClasspath();
        final Packages pkgs = cp.restrictTo(Iterables.transform(packages, Functions.toStringFunction()));

        renderer.list();
        
        final Function<Class<?>, Class<? extends IpcCommand>> asSubClass = Reflection.asSubclass(IpcCommand.class);
        for (Class<? extends IpcCommand> command : Iterables.transform(pkgs.filter(FILTER), asSubClass)) {
            renderer.value(command, CommandRenderer.INSTANCE);
        }
        
        renderer.endList();

        result.put("commands", renderer.build());
    }
    
}
